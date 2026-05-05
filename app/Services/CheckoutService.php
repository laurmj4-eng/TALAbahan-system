<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\VoucherModel;
use App\Models\VoucherRedemptionModel;
use App\Models\PaymentAttemptModel;
use App\Models\ShippingLocationModel;
use App\Models\ProductPaymentConstraintModel;
use App\Models\CodComplianceModel;
use App\Models\UserModel;
use App\Models\OrderStatusHistoryModel;
use Exception;

class CheckoutService
{
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel;
    protected $salesModel;
    protected $voucherModel;
    protected $voucherRedemptionModel;
    protected $paymentAttemptModel;
    protected $shippingLocationModel;
    protected $productPaymentConstraintModel;
    protected $codComplianceModel;
    protected $userModel;
    protected $historyModel;
    protected $emailService;

    private const ALLOWED_PAYMENT_METHODS = ['COD', 'GCASH'];

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
        $this->salesModel = new SalesModel();
        $this->voucherModel = new VoucherModel();
        $this->voucherRedemptionModel = new VoucherRedemptionModel();
        $this->paymentAttemptModel = new PaymentAttemptModel();
        $this->shippingLocationModel = new ShippingLocationModel();
        $this->productPaymentConstraintModel = new ProductPaymentConstraintModel();
        $this->codComplianceModel = new CodComplianceModel();
        $this->userModel = new UserModel();
        $this->historyModel = new OrderStatusHistoryModel();
        $this->emailService = new EmailNotificationService();
    }

    public function buildCheckoutQuote(array $orderData, string $username): array
    {
        $items = $orderData['items'] ?? [];
        if (empty($items)) {
            return ['ok' => false, 'message' => 'Cart is empty.'];
        }

        $shipping = $orderData['shipping_details'] ?? [];
        $phone = trim((string)($shipping['phone'] ?? ''));
        $barangay = trim((string)($shipping['barangay'] ?? ''));
        $receiverName = trim((string)($shipping['name'] ?? $username));
        
        if ($receiverName === '') {
            $receiverName = $username;
        }

        if ($barangay === '') {
            return ['ok' => false, 'message' => 'Shipping location required.'];
        }

        if (!preg_match('/^09\\d{9}$/', $phone)) {
            return ['ok' => false, 'message' => 'Phone number must be in 09XXXXXXXXX format.'];
        }

        $paymentMethod = strtoupper((string)($orderData['payment_method'] ?? 'COD'));
        if (!in_array($paymentMethod, self::ALLOWED_PAYMENT_METHODS, true)) {
            return ['ok' => false, 'message' => 'Unsupported payment method.'];
        }

        $shippingLocation = $this->shippingLocationModel->where('barangay_name', $barangay)
            ->where('is_active', 1)
            ->first();

        if (!$shippingLocation) {
            $shippingLocation = $this->shippingLocationModel->like('barangay_name', $barangay)
                ->where('is_active', 1)
                ->first();
        }

        if (!$shippingLocation) {
            return ['ok' => false, 'message' => 'Sorry, we do not ship to this location.'];
        }

        $lineItems = [];
        $subtotal = 0.0;
        $productIds = [];

        foreach ($items as $item) {
            $productId = (int)($item['id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                return ['ok' => false, 'message' => 'Invalid product or quantity detected in cart.'];
            }

            $product = $this->productModel->find($productId);
            if (!$product) {
                return ['ok' => false, 'message' => 'One of the items is no longer available.'];
            }

            if ((float)$product['current_stock'] < $quantity) {
                return ['ok' => false, 'message' => "Not enough stock for {$product['name']}."];
            }

            $unitPrice = (float)$product['selling_price'];
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

        $constraints = $this->productPaymentConstraintModel->getAllowedByProductIds($productIds);
        $allowedMethods = null;

        foreach ($productIds as $id) {
            $row = $constraints[$id] ?? [];
            if (empty($row)) {
                continue;
            }
            $allowedMethods = $allowedMethods === null ? $row : array_values(array_intersect($allowedMethods, $row));
        }

        if (is_array($allowedMethods) && !empty($allowedMethods) && !in_array($paymentMethod, $allowedMethods, true)) {
            return ['ok' => false, 'message' => 'Your cart has mixed payment requirements. Please split checkout by payment method.'];
        }

        if ($paymentMethod === 'COD' && !$this->codComplianceModel->isCodAllowed($username)) {
            return ['ok' => false, 'message' => 'COD is temporarily unavailable for your account. Please use GCash.'];
        }

        $shippingFee = round((float)($shippingLocation['shipping_fee'] ?? 49), 2);
        $eligibleVouchers = $this->voucherModel->getEligibleForQuote((float)$subtotal, $paymentMethod);
        $pickedByScope = $this->voucherModel->pickBestByScope($eligibleVouchers, (float)$subtotal);

        $requestedCode = strtoupper(trim((string)($orderData['voucher_code'] ?? '')));
        $requestedVoucher = null;

        if ($requestedCode !== '') {
            foreach ($eligibleVouchers as $voucher) {
                if (strtoupper((string)$voucher['code']) === $requestedCode) {
                    $requestedVoucher = $voucher;
                    $requestedVoucher['computed_discount'] = $this->voucherModel->computeDiscount($requestedVoucher, (float)$subtotal);
                    break;
                }
            }
        }

        $applied = [];
        if ($requestedVoucher) {
            $applied[] = $requestedVoucher;
            $scope = strtolower((string)$requestedVoucher['scope']);
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
            $discount = (float)($voucher['computed_discount'] ?? 0);
            if ($discount <= 0) {
                continue;
            }
            $voucherDiscount += $discount;
            $appliedList[] = [
                'id' => (int)$voucher['id'],
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
                'allowed_methods' => $allowedMethods === null || empty($allowedMethods) ? self::ALLOWED_PAYMENT_METHODS : $allowedMethods,
            ],
        ];
    }

    public function placeOrder(array $quoteData, string $username): array
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $transactionCode = 'ORD-' . strtoupper(uniqid());
            $paymentMethod = strtoupper((string)$quoteData['payment_method']);
            $paymentStatus = $paymentMethod === 'COD' ? 'pending_confirmation' : 'paid';
            $paymentProvider = $paymentMethod === 'COD' ? null : $paymentMethod;
            $paymentRef = null;

            if ($paymentMethod === 'GCASH') {
                $paymentResult = $this->simulateGcashPayment((float)$quoteData['final_total'], $transactionCode);
                if (!$paymentResult['success']) {
                    throw new Exception('GCash payment failed.');
                }
                $paymentRef = $paymentResult['transaction_id'] ?? null;
            }

            $orderId = $this->orderModel->insert([
                'transaction_code' => $transactionCode,
                'customer_name' => $quoteData['receiver_name'],
                'total_amount' => (float)$quoteData['final_total'],
                'subtotal_amount' => (float)$quoteData['subtotal'],
                'shipping_fee' => (float)$quoteData['shipping_fee'],
                'voucher_discount' => (float)$quoteData['voucher_discount'],
                'final_amount' => (float)$quoteData['final_total'],
                'status' => OrderModel::STATUS_PENDING,
                'notes' => 'Customer online order',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_ref' => $paymentRef,
                'payment_provider' => $paymentProvider,
                'applied_vouchers' => json_encode($quoteData['applied_vouchers']),
                'shipping_barangay' => $quoteData['shipping_barangay'],
                'shipping_phone' => $quoteData['shipping_phone'],
            ], true);

            if (!$orderId) {
                $errors = $this->orderModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Unknown database error.';
                throw new Exception('Failed to create order: ' . $errorMsg);
            }

            // Log initial status
            $this->historyModel->logStatusChange($orderId, 'N/A', OrderModel::STATUS_PENDING, $quoteData['receiver_name'], 'Order placed via online portal');

            foreach ($quoteData['items'] as $item) {
                $row = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ];

                if (!$this->orderItemModel->insert($row)) {
                    throw new Exception('Failed to save order line items.');
                }

                $builder = $db->table('products');
                $builder->set('current_stock', 'current_stock - ' . (int)$item['quantity'], false);
                $builder->where('id', (int)$item['product_id']);
                $builder->where('current_stock >=', (int)$item['quantity']);
                $builder->update();

                if ($db->affectedRows() !== 1) {
                    throw new Exception("Stock changed before checkout for {$item['product_name']}.");
                }
            }

            foreach ($quoteData['applied_vouchers'] as $voucher) {
                $this->voucherRedemptionModel->insert([
                    'voucher_id' => (int)$voucher['id'],
                    'order_id' => (int)$orderId,
                    'customer_name' => $quoteData['receiver_name'],
                    'discount_amount' => (float)$voucher['discount'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->paymentAttemptModel->insert([
                'order_id' => (int)$orderId,
                'payment_method' => $paymentMethod,
                'provider' => $paymentProvider,
                'amount' => (float)$quoteData['final_total'],
                'status' => $paymentStatus === 'paid' ? 'success' : 'pending',
                'reference' => $paymentRef,
                'message' => $paymentStatus === 'paid' ? 'Payment settled at checkout.' : 'Awaiting COD collection.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$this->salesModel->recordFromOrder($transactionCode, array_column($quoteData['items'], 'product_name'), (float)$quoteData['final_total'])) {
                throw new Exception('Failed to record sales history.');
            }

            $db->transCommit();
            
            $this->sendOrderConfirmation($username, $quoteData, $transactionCode);
            
            return [
                'ok' => true,
                'transaction_code' => $transactionCode,
                'message' => $paymentMethod === 'GCASH' ? 'GCash payment successful. Order placed.' : 'Order placed!',
            ];
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
    
    protected function sendOrderConfirmation(string $username, array $quoteData, string $transactionCode): void
    {
        $user = $this->userModel->where('username', $username)->first();
        
        if ($user && !empty($user['email'])) {
            $customerEmail = $user['email'];
            $customerName = $quoteData['receiver_name'];
            $totalAmount = (float)$quoteData['final_total'];
            
            try {
                $this->emailService->sendOrderConfirmation(
                    $customerEmail,
                    $customerName,
                    $transactionCode,
                    $totalAmount
                );
            } catch (Exception $e) {
                log_message('error', '[CheckoutService] Failed to send order confirmation email: ' . $e->getMessage());
            }
        }
    }

    private function simulateGcashPayment(float $amount, string $refCode): array
    {
        usleep(1500000);
        return [
            'success' => true,
            'transaction_id' => 'GC-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $amount,
            'ref_code' => $refCode
        ];
    }
}
