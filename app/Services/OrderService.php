<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\UserModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\DamagedLedgerModel;
use App\Models\LossesModel;
use Exception;

class OrderService
{
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel;
    protected $salesModel;
    protected $userModel;
    protected $historyModel;
    protected $damagedLedgerModel;
    protected $lossesModel;
    protected $emailService;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
        $this->salesModel = new SalesModel();
        $this->userModel = new UserModel();
        $this->historyModel = new OrderStatusHistoryModel();
        $this->damagedLedgerModel = new DamagedLedgerModel();
        $this->lossesModel = new LossesModel();
        $this->emailService = new EmailNotificationService();
    }

    public function updateOrderStatus(int $orderId, string $status): array
    {
        $validStatuses = [
            OrderModel::STATUS_PENDING,
            OrderModel::STATUS_PROCESSING,
            OrderModel::STATUS_SHIPPED,
            OrderModel::STATUS_COMPLETED,
            OrderModel::STATUS_CANCELLED,
            OrderModel::STATUS_REFUNDED
        ];

        if (!in_array($status, $validStatuses, true)) {
            return ['ok' => false, 'message' => 'Invalid order status.'];
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return ['ok' => false, 'message' => 'Order not found.'];
        }

        $oldStatus = $order['status'];
        
        $db = db_connect();
        $db->transBegin();

        try {
            $updateData = ['status' => $status];
            
            if ($status === OrderModel::STATUS_SHIPPED && empty($order['shipped_at'])) {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            }
            
            if ($status === OrderModel::STATUS_COMPLETED) {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                if (($order['payment_method'] ?? 'COD') === 'COD') {
                    $updateData['payment_status'] = 'paid';
                }
            }

            if (!$this->orderModel->update($orderId, $updateData)) {
                throw new Exception('Failed to update order status.');
            }

            // --- AUTO-TRIGGER LOGIC ---
            // 1. If cancelled, restore stock automatically
            if ($status === OrderModel::STATUS_CANCELLED && $oldStatus !== OrderModel::STATUS_CANCELLED) {
                $orderItems = $this->orderItemModel->where('order_id', $orderId)->findAll();
                foreach ($orderItems as $item) {
                    $this->productModel->increaseStock((int)$item['product_id'], (float)$item['quantity']);
                }
                
                // 2. If cancelling from Processing status, record as food waste loss
                if ($oldStatus === OrderModel::STATUS_PROCESSING) {
                    $recordedBy = session()->get('username') ?? 'System';
                    $this->lossesModel->recordFoodWasteLoss(
                        $orderId,
                        (float) $order['total_amount'],
                        $recordedBy,
                        'Order cancelled by admin after cooking started'
                    );
                }
            }

            // 2. Log status change
            $changedBy = session()->get('username') ?? 'System';
            $this->historyModel->logStatusChange($orderId, $oldStatus, $status, $changedBy);

            if ($db->transStatus() === false) {
                throw new Exception('Transaction failed.');
            }

            $db->transCommit();
            
            if ($oldStatus !== $status) {
                $this->sendStatusChangeNotification($order, $status);
            }
            
            return ['ok' => true, 'message' => 'Status updated successfully.'];
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
    
    protected function sendStatusChangeNotification(array $order, string $newStatus): void
    {
        $user = $this->userModel->where('username', $order['customer_name'])->first();
        
        if ($user && !empty($user['email'])) {
            $customerEmail = $user['email'];
            $customerName = $order['customer_name'];
            $transactionCode = $order['transaction_code'];
            
            try {
                $this->emailService->sendOrderStatusUpdate(
                    $customerEmail,
                    $customerName,
                    $transactionCode,
                    $newStatus
                );
            } catch (Exception $e) {
                log_message('error', '[OrderService] Failed to send status update email: ' . $e->getMessage());
            }
        }
    }

    public function cancelOrder(int $orderId, string $reason = '', string $customerName = ''): array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return ['ok' => false, 'message' => 'Order not found.'];
        }

        if ($customerName && $order['customer_name'] !== $customerName) {
            return ['ok' => false, 'message' => 'Access denied.'];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $items = $this->orderItemModel->where('order_id', $orderId)->findAll();
            foreach ($items as $item) {
                if (!$this->productModel->increaseStock((int)$item['product_id'], (float)$item['quantity'])) {
                    throw new Exception("Failed to restore stock for {$item['product_name']}.");
                }
            }

            $cancelReason = $reason ?: 'Cancelled by customer';
            if (!$this->orderModel->update($orderId, [
                'status' => OrderModel::STATUS_CANCELLED,
                'cancel_reason' => $cancelReason
            ])) {
                throw new Exception('Failed to cancel order.');
            }

            // Log status change
            $changedBy = $customerName ?: (session()->get('username') ?? 'System');
            $this->historyModel->logStatusChange($orderId, $order['status'], OrderModel::STATUS_CANCELLED, $changedBy, $cancelReason);

            $db->transCommit();
            return ['ok' => true, 'message' => 'Order cancelled successfully.'];
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function getOrderWithDetails(int $orderId): ?array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return null;
        }

        $order['items'] = $this->orderItemModel->getItemsByOrder($orderId);
        return $order;
    }

    public function updateTracking(int $orderId, ?string $trackingNumber, ?string $courierName): array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return ['ok' => false, 'message' => 'Order not found.'];
        }

        $oldStatus = $order['status'];
        $data = [
            'tracking_number' => $trackingNumber ?: null,
            'courier_name' => $courierName ?: null,
        ];

        if (($data['tracking_number'] || $data['courier_name']) && $order['status'] === OrderModel::STATUS_PROCESSING) {
            $data['status'] = OrderModel::STATUS_SHIPPED;
            $data['shipped_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->orderModel->update($orderId, $data)) {
            return ['ok' => false, 'message' => 'Failed to update tracking.'];
        }

        // Log status change if tracking triggered a status change
        if (isset($data['status']) && $oldStatus !== $data['status']) {
            $changedBy = session()->get('username') ?? 'System';
            $remarks = "Tracking: {$trackingNumber} ({$courierName})";
            $this->historyModel->logStatusChange($orderId, $oldStatus, $data['status'], $changedBy, $remarks);
        }

        if (isset($data['status']) && $oldStatus !== $data['status']) {
            $this->sendStatusChangeNotification($order, $data['status']);
        }

        return ['ok' => true, 'message' => 'Tracking details updated.'];
    }

    /**
     * Cancel an order due to items being damaged in transit
     */
    public function cancelDamagedInTransit(int $orderId, string $recordedBy = null, bool $issueRedelivery = false): array
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return ['ok' => false, 'message' => 'Order not found.'];
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $oldStatus = $order['status'];

            $orderItems = $this->orderItemModel->where('order_id', $orderId)->findAll();

            if (!$this->orderModel->update($orderId, [
                'status'        => OrderModel::STATUS_CANCELLED,
                'cancel_reason' => 'Damaged in transit',
            ])) {
                throw new Exception('Failed to update order status.');
            }

            $this->damagedLedgerModel->recordDamagedOrder($orderId, $orderItems, $recordedBy);

            $changedBy = $recordedBy ?? (session()->get('username') ?? 'System');
            $this->historyModel->logStatusChange(
                $orderId,
                $oldStatus,
                OrderModel::STATUS_CANCELLED,
                $changedBy,
                'Damaged in transit'
            );

            if ($issueRedelivery) {
                $newTransactionCode = $order['transaction_code'] . '-RE';
                $newOrderData = [
                    'transaction_code' => $newTransactionCode,
                    'customer_name' => $order['customer_name'],
                    'customer_alias' => $order['customer_alias'],
                    'user_id' => $order['user_id'],
                    'subtotal_amount' => $order['subtotal_amount'],
                    'shipping_fee' => $order['shipping_fee'],
                    'voucher_discount' => $order['voucher_discount'],
                    'final_amount' => $order['final_amount'],
                    'total_amount' => $order['total_amount'],
                    'status' => OrderModel::STATUS_PROCESSING,
                    'notes' => 'Free redelivery for damaged order: ' . $order['transaction_code'],
                    'payment_method' => $order['payment_method'],
                    'payment_status' => 'Paid',
                    'payment_ref' => $order['payment_ref'],
                    'payment_provider' => $order['payment_provider'],
                    'applied_vouchers' => $order['applied_vouchers'],
                    'tracking_number' => null,
                    'courier_name' => null,
                    'shipping_barangay' => $order['shipping_barangay'],
                    'shipping_city' => $order['shipping_city'],
                    'shipping_street' => $order['shipping_street'],
                    'shipping_phone' => $order['shipping_phone'],
                    'is_replacement' => 1,
                    'replaces_order_id' => $orderId,
                ];

                if (!$this->orderModel->insert($newOrderData)) {
                    throw new Exception('Failed to create replacement order.');
                }

                $newOrderId = (int)$this->orderModel->getInsertID();

                foreach ($orderItems as $item) {
                    $newItem = $item;
                    unset($newItem['id']);
                    $newItem['order_id'] = $newOrderId;
                    if (!$this->orderItemModel->insert($newItem)) {
                        throw new Exception('Failed to duplicate order items for replacement.');
                    }
                }

                $this->historyModel->logStatusChange(
                    $newOrderId,
                    null,
                    OrderModel::STATUS_PROCESSING,
                    $changedBy,
                    'Free redelivery created'
                );
            }

            if ($db->transStatus() === false) {
                throw new Exception('Transaction failed.');
            }

            $db->transCommit();

            $this->sendStatusChangeNotification($order, OrderModel::STATUS_CANCELLED);

            $message = 'Order cancelled due to damage in transit.';
            if ($issueRedelivery) {
                $message .= ' Free redelivery order created.';
            }

            return ['ok' => true, 'message' => $message];
        } catch (Exception $e) {
            $db->transRollback();
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
