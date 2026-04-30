<?php

namespace App\Services;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\UserModel;
use Exception;

class OrderService
{
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel;
    protected $salesModel;
    protected $userModel;
    protected $emailService;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
        $this->salesModel = new SalesModel();
        $this->userModel = new UserModel();
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

        if (isset($data['status']) && $oldStatus !== $data['status']) {
            $this->sendStatusChangeNotification($order, $data['status']);
        }

        return ['ok' => true, 'message' => 'Tracking details updated.'];
    }
}
