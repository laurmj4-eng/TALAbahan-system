<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderReviewModel;
use App\Models\RefundRequestModel;
use App\Models\PaymentAttemptModel;

class Orders extends BaseController
{
    private const REMORSE_WINDOW_MINUTES = 60;
    private const REFUND_WINDOW_DAYS = 7;
    private const ORDER_CENTER_TABS = ['all', 'to_pay', 'to_ship', 'to_receive', 'completed'];

    public function orderItems()
    {
        return $this->orderCenter();
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
        $dashboard = new Dashboard();
        $counts = $dashboard->getCustomerOrderCounts($customerName);
        $orders = $this->getOrdersForCustomerByTab($customerName, $tab);

        return view('customer/order_items', [
            'title' => 'Order Center',
            'username' => $customerName,
            'orders' => $orders,
            'activeTab' => $tab,
            'counts' => $counts,
            'isAJAX' => $this->request->isAJAX(),
        ]);
    }

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

    public function buildLifecycleState(array $order): array
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

    public function buildOrderTimeline(array $order): array
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

    public function getOrdersForCustomerByTab(string $customerName, string $tab): array
    {
        $orderModel = new OrderModel();
        $orderModel->where('customer_name', $customerName);

        if ($tab === 'to_pay') {
            $orderModel->where('status', OrderModel::STATUS_PENDING);
            $db = db_connect();
            if ($db->fieldExists('payment_status', 'orders')) {
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

    private function simulateGcashPayment($amount, $refCode)
    {
        usleep(1500000); // 1.5 seconds
        return [
            'success' => true,
            'transaction_id' => 'GC-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $amount,
            'ref_code' => $refCode
        ];
    }
}
