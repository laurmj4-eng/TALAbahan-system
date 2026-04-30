<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\CodComplianceModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PaymentAttemptModel;
use App\Models\ProductModel;
use App\Models\ProductPaymentConstraintModel;
use App\Models\SalesModel;
use App\Models\ShippingLocationModel;
use App\Models\VoucherModel;
use App\Models\VoucherRedemptionModel;

class Dashboard extends BaseController
{
    private const ALLOWED_PAYMENT_METHODS = ['COD', 'GCASH'];

    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch products and shippable locations
        $productModel = new ProductModel();
        $shippingModel = new \App\Models\ShippingLocationModel();

        // 3. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $productModel->findAll(),
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll()
        ];

        // 4. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }

    public function orderItems()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $orderModel = new \App\Models\OrderModel();
        
        // Fetch only orders for this customer
        $orders = $orderModel->where('customer_name', session()->get('username'))
                            ->orderBy('created_at', 'DESC')
                            ->findAll();

        // 3. Prepare data for the view
        $data =[
            'title'    => 'My Orders',
            'username' => session()->get('username'),
            'orders'   => $orders
        ];

        // 4. Load the customer order items view (now as My Orders)
        return view('customer/order_items', $data);
    }

    public function preCheckout()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $quote = $this->buildCheckoutQuote();
        if (! $quote['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $quote['message']])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $quote['data'],
        ]);
    }

    public function placeOrder()
    {
        if (session()->get('role') !== 'customer' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $quote = $this->buildCheckoutQuote();
        if (! $quote['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $quote['message']])->setStatusCode(400);
        }

        $data = $quote['data'];
        $orderModel = new OrderModel();
        $orderItemModel = new OrderItemModel();
        $salesModel = new SalesModel();
        $voucherRedemptionModel = new VoucherRedemptionModel();
        $paymentAttemptModel = new PaymentAttemptModel();
        $db = db_connect();

        $transactionCode = 'ORD-' . strtoupper(uniqid());
        $paymentMethod = strtoupper((string) $data['payment_method']);
        $paymentStatus = $paymentMethod === 'COD' ? 'pending_confirmation' : 'paid';
        $paymentProvider = $paymentMethod === 'COD' ? null : $paymentMethod;
        $paymentRef = null;

        if ($paymentMethod === 'GCASH') {
            $paymentResult = $this->simulateGcashPayment((float) $data['final_total'], $transactionCode);
            if (! $paymentResult['success']) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'GCash payment failed.'])->setStatusCode(400);
            }
            $paymentRef = $paymentResult['transaction_id'] ?? null;
        }

        $db->transBegin();
        try {
            $orderId = $orderModel->insert([
                'transaction_code' => $transactionCode,
                'customer_name' => $data['receiver_name'],
                'total_amount' => (float) $data['final_total'],
                'subtotal_amount' => (float) $data['subtotal'],
                'shipping_fee' => (float) $data['shipping_fee'],
                'voucher_discount' => (float) $data['voucher_discount'],
                'final_amount' => (float) $data['final_total'],
                'status' => 'Pending',
                'notes' => 'Customer online order',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_ref' => $paymentRef,
                'payment_provider' => $paymentProvider,
                'applied_vouchers' => json_encode($data['applied_vouchers']),
                'shipping_barangay' => $data['shipping_barangay'],
                'shipping_phone' => $data['shipping_phone'],
            ], true);

            if (! $orderId) {
                throw new \RuntimeException('Failed to create order.');
            }

            foreach ($data['items'] as $item) {
                $row = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ];
                if (! $orderItemModel->insert($row)) {
                    throw new \RuntimeException('Failed to save order line items.');
                }

                $builder = $db->table('products');
                $builder->set('current_stock', 'current_stock - ' . (int) $item['quantity'], false);
                $builder->where('id', (int) $item['product_id']);
                $builder->where('current_stock >=', (int) $item['quantity']);
                $builder->update();
                if ($db->affectedRows() !== 1) {
                    throw new \RuntimeException("Stock changed before checkout for {$item['product_name']}.");
                }
            }

            foreach ($data['applied_vouchers'] as $voucher) {
                $voucherRedemptionModel->insert([
                    'voucher_id' => (int) $voucher['id'],
                    'order_id' => (int) $orderId,
                    'customer_name' => $data['receiver_name'],
                    'discount_amount' => (float) $voucher['discount'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $paymentAttemptModel->insert([
                'order_id' => (int) $orderId,
                'payment_method' => $paymentMethod,
                'provider' => $paymentProvider,
                'amount' => (float) $data['final_total'],
                'status' => $paymentStatus === 'paid' ? 'success' : 'pending',
                'reference' => $paymentRef,
                'message' => $paymentStatus === 'paid' ? 'Payment settled at checkout.' : 'Awaiting COD collection.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (! $salesModel->recordFromOrder($transactionCode, array_column($data['items'], 'product_name'), (float) $data['final_total'])) {
                throw new \RuntimeException('Failed to record sales history.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order transaction failed.'])->setStatusCode(500);
        }

        $db->transCommit();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $paymentMethod === 'GCASH' ? 'GCash payment successful. Order placed.' : 'Order placed!',
            'transaction_code' => $transactionCode,
        ]);
    }

    private function buildCheckoutQuote(): array
    {
        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode((string) $orderDataJson, true);
        if (! is_array($orderData)) {
            return ['ok' => false, 'message' => 'Invalid checkout payload.'];
        }

        $items = $orderData['items'] ?? [];
        if ($items === []) {
            return ['ok' => false, 'message' => 'Cart is empty.'];
        }

        $shipping = $orderData['shipping_details'] ?? [];
        $phone = trim((string) ($shipping['phone'] ?? ''));
        $barangay = trim((string) ($shipping['barangay'] ?? ''));
        $receiverName = trim((string) ($shipping['name'] ?? session()->get('username')));
        if ($receiverName === '') {
            $receiverName = (string) session()->get('username');
        }
        if ($barangay === '') {
            return ['ok' => false, 'message' => 'Shipping location required.'];
        }
        if (! preg_match('/^09\d{9}$/', $phone)) {
            return ['ok' => false, 'message' => 'Phone number must be in 09XXXXXXXXX format.'];
        }

        $paymentMethod = strtoupper((string) ($orderData['payment_method'] ?? 'COD'));
        if (! in_array($paymentMethod, self::ALLOWED_PAYMENT_METHODS, true)) {
            return ['ok' => false, 'message' => 'Unsupported payment method.'];
        }

        $shippingModel = new ShippingLocationModel();
        $shippingLocation = $shippingModel->where('barangay_name', $barangay)->where('is_active', 1)->first();
        if (! $shippingLocation) {
            $shippingLocation = $shippingModel->like('barangay_name', $barangay)->where('is_active', 1)->first();
        }
        if (! $shippingLocation) {
            return ['ok' => false, 'message' => 'Sorry, we do not ship to this location.'];
        }

        $productModel = new ProductModel();
        $lineItems = [];
        $subtotal = 0.0;
        $productIds = [];
        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $quantity <= 0) {
                return ['ok' => false, 'message' => 'Invalid product or quantity detected in cart.'];
            }

            $product = $productModel->find($productId);
            if (! $product) {
                return ['ok' => false, 'message' => 'One of the items is no longer available.'];
            }
            if ((float) $product['current_stock'] < $quantity) {
                return ['ok' => false, 'message' => "Not enough stock for {$product['name']}."];
            }

            $unitPrice = (float) $product['selling_price'];
            $lineSubtotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineSubtotal;
            $productIds[] = $productId;

            $lineItems[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit' => $product['unit'] ?: 'piece',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $lineSubtotal,
            ];
        }

        $constraintModel = new ProductPaymentConstraintModel();
        $constraints = $constraintModel->getAllowedByProductIds($productIds);
        $allowedMethods = null;
        foreach ($productIds as $id) {
            $row = $constraints[$id] ?? [];
            if ($row === []) {
                continue;
            }
            $allowedMethods = $allowedMethods === null ? $row : array_values(array_intersect($allowedMethods, $row));
        }
        if (is_array($allowedMethods) && $allowedMethods !== [] && ! in_array($paymentMethod, $allowedMethods, true)) {
            return ['ok' => false, 'message' => 'Your cart has mixed payment requirements. Please split checkout by payment method.'];
        }

        $codComplianceModel = new CodComplianceModel();
        if ($paymentMethod === 'COD' && ! $codComplianceModel->isCodAllowed((string) session()->get('username'))) {
            return ['ok' => false, 'message' => 'COD is temporarily unavailable for your account. Please use GCash.'];
        }

        $shippingFee = round((float) ($shippingLocation['shipping_fee'] ?? 49), 2);
        $voucherModel = new VoucherModel();
        $eligibleVouchers = $voucherModel->getEligibleForQuote((float) $subtotal, $paymentMethod);
        $pickedByScope = $voucherModel->pickBestByScope($eligibleVouchers, (float) $subtotal);

        $requestedCode = strtoupper(trim((string) ($orderData['voucher_code'] ?? '')));
        $requestedVoucher = null;
        if ($requestedCode !== '') {
            foreach ($eligibleVouchers as $voucher) {
                if (strtoupper((string) $voucher['code']) === $requestedCode) {
                    $requestedVoucher = $voucher;
                    $requestedVoucher['computed_discount'] = $voucherModel->computeDiscount($requestedVoucher, (float) $subtotal);
                    break;
                }
            }
        }

        $applied = [];
        if ($requestedVoucher) {
            $applied[] = $requestedVoucher;
            $scope = strtolower((string) $requestedVoucher['scope']);
            $otherScope = $scope === 'platform' ? 'shop' : 'platform';
            if (isset($pickedByScope[$otherScope]) && $pickedByScope[$otherScope]) {
                $applied[] = $pickedByScope[$otherScope];
            }
        } else {
            foreach (['platform', 'shop'] as $scope) {
                if (isset($pickedByScope[$scope]) && $pickedByScope[$scope]) {
                    $applied[] = $pickedByScope[$scope];
                }
            }
        }

        $voucherDiscount = 0.0;
        $appliedList = [];
        foreach ($applied as $voucher) {
            $discount = (float) ($voucher['computed_discount'] ?? 0);
            if ($discount <= 0) {
                continue;
            }
            $voucherDiscount += $discount;
            $appliedList[] = [
                'id' => (int) $voucher['id'],
                'code' => $voucher['code'],
                'name' => $voucher['name'],
                'scope' => $voucher['scope'],
                'discount' => round($discount, 2),
            ];
        }

        $grossTotal = round($subtotal + $shippingFee, 2);
        $voucherDiscount = min($grossTotal, round($voucherDiscount, 2));
        $finalTotal = max(0, round($grossTotal - $voucherDiscount, 2));

        return [
            'ok' => true,
            'data' => [
                'receiver_name' => $receiverName,
                'shipping_phone' => $phone,
                'shipping_barangay' => $shippingLocation['barangay_name'],
                'payment_method' => $paymentMethod,
                'items' => $lineItems,
                'subtotal' => round($subtotal, 2),
                'shipping_fee' => $shippingFee,
                'voucher_discount' => $voucherDiscount,
                'final_total' => $finalTotal,
                'applied_vouchers' => $appliedList,
                'allowed_methods' => $allowedMethods === null || $allowedMethods === [] ? self::ALLOWED_PAYMENT_METHODS : $allowedMethods,
            ],
        ];
    }

    /**
     * Validate if the detected barangay is shippable
     */
    public function validateLocation()
    {
        $barangay = trim($this->request->getPost('barangay'));
        if (empty($barangay)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No location detected']);
        }

        $shippingModel = new ShippingLocationModel();
        
        // Simple search - MySQL is case-insensitive by default for VARCHAR
        $location = $shippingModel->where('barangay_name', $barangay)
                                 ->where('is_active', 1)
                                 ->first();

        // If not found, try a broader search just in case
        if (!$location) {
            $location = $shippingModel->like('barangay_name', $barangay)
                                     ->where('is_active', 1)
                                     ->first();
        }

        if ($location) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Location not supported']);
    }

    /**
     * Fetch order details for the modal
     */
    public function orderDetails($orderId)
    {
        if (session()->get('role') !== 'customer') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $orderItemModel = new OrderItemModel();

        $order = $orderModel->where('id', $orderId)
                           ->where('customer_name', session()->get('username'))
                           ->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $items = $orderItemModel->where('order_id', $orderId)->findAll();
        $order['items'] = $items;

        return $this->response->setJSON(['status' => 'success', 'data' => $order]);
    }

    /**
     * Cancel a pending order
     */
    public function cancelOrder()
    {
        if (session()->get('role') !== 'customer' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderId = $this->request->getPost('id');
        $orderModel = new OrderModel();
        $orderItemModel = new OrderItemModel();
        $db = db_connect();

        $order = $orderModel->where('id', $orderId)
                           ->where('customer_name', session()->get('username'))
                           ->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        if ($order['status'] !== 'Pending') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Only pending orders can be cancelled.'])->setStatusCode(400);
        }

        $db->transBegin();
        try {
            // Return stock before cancelling
            $items = $orderItemModel->where('order_id', $orderId)->findAll();
            foreach ($items as $item) {
                $builder = $db->table('products');
                $builder->set('current_stock', 'current_stock + ' . (int) $item['quantity'], false);
                $builder->where('id', (int) $item['product_id']);
                $builder->update();

                if ($db->affectedRows() !== 1) {
                    throw new \RuntimeException("Failed to restore stock for product ID {$item['product_id']}.");
                }
            }

            if (! $orderModel->update($orderId, ['status' => 'Cancelled'])) {
                throw new \RuntimeException('Failed to cancel order.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cancellation transaction failed.'])->setStatusCode(500);
        }

        $db->transCommit();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully.']);
    }

    /**
     * Temporary Mock GCash API Simulation
     */
    private function simulateGcashPayment($amount, $refCode)
    {
        // Simulate external API call delay
        usleep(1500000); // 1.5 seconds

        // Simulate success for now
        return [
            'success' => true,
            'transaction_id' => 'GC-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $amount,
            'ref_code' => $refCode
        ];
    }
}