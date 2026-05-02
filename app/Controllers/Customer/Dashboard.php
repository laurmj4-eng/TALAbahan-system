<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\CodComplianceModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\OrderReviewModel;
use App\Models\PaymentAttemptModel;
use App\Models\ProductModel;
use App\Models\ProductPaymentConstraintModel;
use App\Models\RefundRequestModel;
use App\Models\SalesModel;
use App\Models\ShippingLocationModel;
use App\Models\VoucherModel;
use App\Models\VoucherRedemptionModel;
use App\Services\CheckoutService;

class Dashboard extends BaseController
{
    private const ALLOWED_PAYMENT_METHODS = ['COD', 'GCASH'];
    private const REMORSE_WINDOW_MINUTES = 60;
    private const REFUND_WINDOW_DAYS = 7;
    private const ORDER_CENTER_TABS = ['all', 'to_pay', 'to_ship', 'to_receive', 'completed'];

    protected $checkoutService;
    private static array $orderColumnCache = [];

    public function __construct()
    {
        $this->checkoutService = new CheckoutService();
    }

    private function orderColumnExists(string $column): bool
    {
        if (isset(self::$orderColumnCache[$column])) {
            return (bool) self::$orderColumnCache[$column];
        }

        $db = db_connect();
        $exists = $db->fieldExists($column, 'orders');
        self::$orderColumnCache[$column] = $exists ? 1 : 0;
        return $exists;
    }

    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch products and shippable locations
        $productModel = new ProductModel();
        $shippingModel = new \App\Models\ShippingLocationModel();
        $customerName = (string) session()->get('username');
        $orderCounts = $this->getCustomerOrderCounts($customerName);
        $activeOrdersCount = (int) ($orderCounts['to_pay'] + $orderCounts['to_ship'] + $orderCounts['to_receive']);

        // 3. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $productModel->findAll(),
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll(),
            'orderCounts'       => $orderCounts,
            'activeOrdersCount' => $activeOrdersCount,
        ];

        // 4. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }

    public function orderItems()
    {
        // Backward compatibility: existing URL now shows the new Order Center
        return $this->orderCenter();
    }

    public function profile()
    {
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $customerName = (string) session()->get('username');
        $counts = $this->getCustomerOrderCounts($customerName);

        return view('customer/profile', [
            'title' => 'My Profile',
            'username' => $customerName,
            'counts' => $counts,
        ]);
    }

    public function orderCenter()
    {
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $tab = strtolower(trim((string) $this->request->getGet('tab')));
        if ($tab === '' || ! in_array($tab, self::ORDER_CENTER_TABS, true)) {
            $tab = 'all';
        }

        $customerName = (string) session()->get('username');
        $counts = $this->getCustomerOrderCounts($customerName);
        $orders = $this->getOrdersForCustomerByTab($customerName, $tab);

        return view('customer/order_items', [
            'title' => 'Order Center',
            'username' => $customerName,
            'orders' => $orders,
            'activeTab' => $tab,
            'counts' => $counts,
        ]);
    }

    public function preCheckout()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode((string) $orderDataJson, true);
        
        if (! is_array($orderData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid checkout payload.'])->setStatusCode(400);
        }

        $username = session()->get('username');
        $quote = $this->checkoutService->buildCheckoutQuote($orderData, $username);
        
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

        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode((string) $orderDataJson, true);
        
        if (! is_array($orderData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid checkout payload.'])->setStatusCode(400);
        }

        $username = session()->get('username');
        $quote = $this->checkoutService->buildCheckoutQuote($orderData, $username);
        
        if (! $quote['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $quote['message']])->setStatusCode(400);
        }

        $result = $this->checkoutService->placeOrder($quote['data'], $username);
        
        if (! $result['ok']) {
            return $this->response->setJSON(['status' => 'error', 'message' => $result['message']])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $result['message'],
            'transaction_code' => $result['transaction_code'],
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
        $order['lifecycle'] = $this->buildLifecycleState($order);
        $order['timeline'] = $this->buildOrderTimeline($order);

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

        $lifecycle = $this->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_cancel'] ?? false)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cancellation window has closed. Please contact seller support.',
            ])->setStatusCode(400);
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

            $cancelReason = trim((string) $this->request->getPost('reason'));
            if (! $orderModel->update($orderId, [
                'status' => 'Cancelled',
                'cancel_reason' => $cancelReason === '' ? 'Cancelled by customer' : $cancelReason,
            ])) {
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

    public function payNow()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderId = (int) $this->request->getPost('id');
        $orderModel = new OrderModel();
        $paymentAttemptModel = new PaymentAttemptModel();

        $order = $orderModel->where('id', $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $lifecycle = $this->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_pay_now'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order is not eligible for Pay Now.'])->setStatusCode(400);
        }

        $paymentResult = $this->simulateGcashPayment((float) ($order['final_amount'] ?? $order['total_amount']), (string) $order['transaction_code']);
        if (! $paymentResult['success']) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Payment failed.'])->setStatusCode(400);
        }

        $updated = $orderModel->update($orderId, [
            'payment_status' => 'paid',
            'payment_method' => 'GCASH',
            'payment_provider' => 'GCASH',
            'payment_ref' => $paymentResult['transaction_id'] ?? null,
        ]);
        if (! $updated) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update payment status.'])->setStatusCode(500);
        }

        $paymentAttemptModel->insert([
            'order_id' => $orderId,
            'payment_method' => 'GCASH',
            'provider' => 'GCASH',
            'amount' => (float) ($order['final_amount'] ?? $order['total_amount']),
            'status' => 'success',
            'reference' => $paymentResult['transaction_id'] ?? null,
            'message' => 'Manual Pay Now completed by customer.',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Payment completed successfully.']);
    }

    public function tracking($orderId)
    {
        if (session()->get('role') !== 'customer') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->where('id', (int) $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $events = [];
        if (! empty($order['shipped_at'])) {
            $events[] = ['label' => 'Picked up by courier', 'at' => $order['shipped_at']];
            $events[] = ['label' => 'Arrived at sorting facility', 'at' => $order['shipped_at']];
            $events[] = ['label' => 'Out for delivery', 'at' => $order['shipped_at']];
        }
        if (! empty($order['delivered_at'])) {
            $events[] = ['label' => 'Delivered', 'at' => $order['delivered_at']];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'tracking_number' => $order['tracking_number'] ?? null,
                'courier_name' => $order['courier_name'] ?? null,
                'events' => $events,
            ],
        ]);
    }

    public function submitReview()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $rating = (int) $this->request->getPost('rating');
        $comment = trim((string) $this->request->getPost('comment'));

        if ($orderId <= 0 || $rating < 1 || $rating > 5) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid review payload.'])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $reviewModel = new OrderReviewModel();
        $order = $orderModel->where('id', $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $lifecycle = $this->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_review'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Review is not available for this order.'])->setStatusCode(400);
        }

        if ($reviewModel->where('order_id', $orderId)->first()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Review already submitted for this order.'])->setStatusCode(409);
        }

        $reviewModel->insert([
            'order_id' => $orderId,
            'customer_name' => (string) session()->get('username'),
            'rating' => $rating,
            'comment' => $comment === '' ? null : $comment,
            'media_paths' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Review submitted.']);
    }

    public function submitRefundRequest()
    {
        if (session()->get('role') !== 'customer' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $reason = trim((string) $this->request->getPost('reason'));
        if ($orderId <= 0 || $reason === '') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Refund reason is required.'])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $refundModel = new RefundRequestModel();
        $order = $orderModel->where('id', $orderId)
            ->where('customer_name', session()->get('username'))
            ->first();
        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $lifecycle = $this->buildLifecycleState($order);
        if (! ($lifecycle['actions']['can_refund_request'] ?? false)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Refund request window has closed.'])->setStatusCode(400);
        }

        $existing = $refundModel->where('order_id', $orderId)
            ->whereIn('status', ['Pending', 'Under Review'])
            ->first();
        if ($existing) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'A refund request is already open for this order.'])->setStatusCode(409);
        }

        $refundModel->insert([
            'order_id' => $orderId,
            'customer_name' => (string) session()->get('username'),
            'reason' => $reason,
            'status' => 'Pending',
            'evidence_paths' => null,
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Refund request submitted.']);
    }

    private function buildLifecycleState(array $order): array
    {
        $status = (string) ($order['status'] ?? OrderModel::STATUS_PENDING);
        $paymentMethod = strtoupper((string) ($order['payment_method'] ?? 'COD'));
        $paymentStatus = strtolower((string) ($order['payment_status'] ?? 'unpaid'));
        $createdAt = strtotime((string) ($order['created_at'] ?? 'now')) ?: time();
        $now = time();

        $cancelDeadlineTs = strtotime('+' . self::REMORSE_WINDOW_MINUTES . ' minutes', $createdAt);
        $paymentTimeoutMinutes = (int) (env('orderLifecycle.paymentTimeoutMinutes') ?: 30);
        $paymentDeadlineTs = strtotime("+{$paymentTimeoutMinutes} minutes", $createdAt);
        $refundWindowDays = (int) (env('orderLifecycle.refundWindowDays') ?: self::REFUND_WINDOW_DAYS);
        $completedAtTs = ! empty($order['delivered_at']) ? strtotime((string) $order['delivered_at']) : ($status === OrderModel::STATUS_COMPLETED ? strtotime((string) ($order['updated_at'] ?? $order['created_at'])) : null);
        $refundDeadlineTs = $completedAtTs ? strtotime("+{$refundWindowDays} days", $completedAtTs) : null;

        $reviewModel = new OrderReviewModel();
        $refundModel = new RefundRequestModel();
        $hasReview = $reviewModel->where('order_id', (int) $order['id'])->first() !== null;
        $hasOpenRefund = $refundModel->where('order_id', (int) $order['id'])
            ->whereIn('status', ['Pending', 'Under Review'])
            ->first() !== null;

        $stageKey = 'closed';
        if ($status === OrderModel::STATUS_PENDING && in_array($paymentStatus, ['unpaid', 'failed', 'pending_confirmation'], true)) {
            $stageKey = 'to_pay';
        } elseif ($status === OrderModel::STATUS_PROCESSING) {
            $stageKey = 'to_ship';
        } elseif ($status === OrderModel::STATUS_SHIPPED) {
            $stageKey = 'in_transit';
        } elseif ($status === OrderModel::STATUS_COMPLETED) {
            $stageKey = 'completed';
        }

        return [
            'stage_key' => $stageKey,
            'payment_deadline_at' => date('Y-m-d H:i:s', $paymentDeadlineTs),
            'cancel_deadline_at' => date('Y-m-d H:i:s', $cancelDeadlineTs),
            'refund_deadline_at' => $refundDeadlineTs ? date('Y-m-d H:i:s', $refundDeadlineTs) : null,
            'actions' => [
                'can_pay_now' => $status === OrderModel::STATUS_PENDING
                    && $paymentMethod !== 'COD'
                    && in_array($paymentStatus, ['unpaid', 'failed', 'pending_confirmation'], true),
                'can_cancel' => $status === OrderModel::STATUS_PROCESSING && $now <= $cancelDeadlineTs,
                'can_track' => $status === OrderModel::STATUS_SHIPPED && trim((string) ($order['tracking_number'] ?? '')) !== '',
                'can_review' => $status === OrderModel::STATUS_COMPLETED && ! $hasReview,
                'can_refund_request' => $status === OrderModel::STATUS_COMPLETED
                    && ! $hasOpenRefund
                    && ($refundDeadlineTs === null || $now <= $refundDeadlineTs),
                'can_contact_seller' => in_array($status, [OrderModel::STATUS_PROCESSING, OrderModel::STATUS_SHIPPED], true),
            ],
        ];
    }

    private function buildOrderTimeline(array $order): array
    {
        $timeline = [];

        $createdAt = (string) ($order['created_at'] ?? '');
        if ($createdAt !== '') {
            $timeline[] = ['label' => 'Order placed', 'at' => $createdAt];
        }

        $paymentStatus = strtolower((string) ($order['payment_status'] ?? ''));
        if (in_array($paymentStatus, ['paid', 'success', 'completed'], true)) {
            $timeline[] = ['label' => 'Payment confirmed', 'at' => (string) ($order['updated_at'] ?? $createdAt)];
        } elseif ($paymentStatus !== '') {
            $timeline[] = ['label' => 'Payment status: ' . strtoupper($paymentStatus), 'at' => (string) ($order['updated_at'] ?? $createdAt)];
        }

        $status = (string) ($order['status'] ?? '');
        if ($status === OrderModel::STATUS_PROCESSING) {
            $timeline[] = ['label' => 'Preparing your order', 'at' => (string) ($order['updated_at'] ?? $createdAt)];
        }

        $shippedAt = (string) ($order['shipped_at'] ?? '');
        if ($shippedAt !== '') {
            $timeline[] = ['label' => 'Shipped', 'at' => $shippedAt];
        }

        $deliveredAt = (string) ($order['delivered_at'] ?? '');
        if ($deliveredAt !== '') {
            $timeline[] = ['label' => 'Delivered', 'at' => $deliveredAt];
        }

        if ($status === OrderModel::STATUS_COMPLETED) {
            $timeline[] = ['label' => 'Completed', 'at' => $deliveredAt !== '' ? $deliveredAt : (string) ($order['updated_at'] ?? $createdAt)];
        } elseif ($status === OrderModel::STATUS_CANCELLED) {
            $timeline[] = ['label' => 'Cancelled', 'at' => (string) ($order['updated_at'] ?? $createdAt)];
        } elseif ($status === OrderModel::STATUS_REFUNDED) {
            $timeline[] = ['label' => 'Refunded', 'at' => (string) ($order['updated_at'] ?? $createdAt)];
        }

        return $timeline;
    }

    private function getCustomerOrderCounts(string $customerName): array
    {
        $orderModel = new OrderModel();

        $all = (int) $orderModel
            ->where('customer_name', $customerName)
            ->countAllResults();

        $toPayQuery = (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PENDING);
        if ($this->orderColumnExists('payment_status')) {
            $toPayQuery->whereIn('payment_status', ['unpaid', 'failed', 'pending_confirmation']);
        }
        $toPay = (int) $toPayQuery->countAllResults();

        $toShip = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_PROCESSING)
            ->countAllResults();

        $toReceive = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_SHIPPED)
            ->countAllResults();

        $completed = (int) (new OrderModel())
            ->where('customer_name', $customerName)
            ->where('status', OrderModel::STATUS_COMPLETED)
            ->countAllResults();

        return [
            'all' => $all,
            'to_pay' => $toPay,
            'to_ship' => $toShip,
            'to_receive' => $toReceive,
            'completed' => $completed,
        ];
    }

    private function getOrdersForCustomerByTab(string $customerName, string $tab): array
    {
        $orderModel = new OrderModel();
        $orderModel->where('customer_name', $customerName);

        if ($tab === 'to_pay') {
            $orderModel->where('status', OrderModel::STATUS_PENDING);
            if ($this->orderColumnExists('payment_status')) {
                $orderModel->whereIn('payment_status', ['unpaid', 'failed', 'pending_confirmation']);
            }
        } elseif ($tab === 'to_ship') {
            $orderModel->where('status', OrderModel::STATUS_PROCESSING);
        } elseif ($tab === 'to_receive') {
            $orderModel->where('status', OrderModel::STATUS_SHIPPED);
        } elseif ($tab === 'completed') {
            $orderModel->where('status', OrderModel::STATUS_COMPLETED);
        }

        return $orderModel->orderBy('created_at', 'DESC')->findAll();
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