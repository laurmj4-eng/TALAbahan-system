<?php

namespace App\Controllers\Traits;

use App\Models\CodComplianceModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\RefundRequestModel;
use App\Services\OrderService;

trait OrderOperationsTrait
{
    protected $orderService;

    public function initializeOrderService()
    {
        $this->orderService = new OrderService();
    }

    public function getOrders()
    {
        $page  = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 15);
        $offset = ($page - 1) * $limit;

        $orderModel = new OrderModel();
        $db = db_connect();

        $total = $db->table('orders o')
            ->select('COUNT(DISTINCT o.id) as cnt')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->get()
            ->getRow()->cnt;

        $orders = $db->table('orders o')
            ->select('o.*, COUNT(oi.id) as item_count')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->groupBy('o.id')
            ->orderBy('o.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Orders fetched.', 'data' => $orders, 'total' => (int) $total, 'page' => $page, 'limit' => $limit]);
    }

    public function getOrderDetail($id)
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->find($id);

        if (!$order) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Order not found',
                'token' => csrf_hash()
            ]);
        }

        $orderItemModel = new OrderItemModel();
        $order['items'] = $orderItemModel->getItemsByOrder($id);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $order,
            'token' => csrf_hash()
        ]);
    }

    public function itemsPage()
    {
        $db = db_connect();

        $rows = $db->table('order_items oi')
            ->select('oi.*, o.transaction_code, o.customer_name, o.status, o.created_at')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return inertia($this->getViewPrefix() . '/OrderItems', [
            'orderItems' => $rows,
            'username' => session()->get('username')
        ]);
    }

    public function items($orderId)
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->find($orderId);

        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found']);
        }

        $orderItemModel = new OrderItemModel();
        $items          = $orderItemModel->getItemsByOrder($orderId);

        $historyModel = new OrderStatusHistoryModel();
        $history      = $historyModel->getHistoryByOrder($orderId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'order_id'   => (int) $orderId,
                'items'      => $items,
                'item_count' => count($items),
                'history'    => $history
            ],
        ]);
    }

    public function updateOrderStatus()
    {
        $json = $this->request->getJSON();
        if ($json) {
            $id = (int) ($json->id ?? 0);
            $status = trim((string) ($json->status ?? ''));
        } else {
            $id     = (int) $this->request->getPost('id');
            $status = trim((string) $this->request->getPost('status'));
        }

        if (!$id || !$status) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid data',
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->find($id);
        if (!$order) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Order not found',
                'token'   => csrf_hash(),
            ])->setStatusCode(404);
        }

        $result = $this->orderService->updateOrderStatus($id, $status);

        if (!$result['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'],
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        if ($status === OrderModel::STATUS_CANCELLED && strtoupper((string)($order['payment_method'] ?? '')) === 'COD') {
            $codComplianceModel = new CodComplianceModel();
            $codComplianceModel->markFailedCod((string)($order['customer_name'] ?? ''));
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $result['message'],
            'token' => csrf_hash()
        ]);
    }

    public function updateTracking()
    {
        $id = (int) $this->request->getPost('id');
        $trackingNumber = trim((string) $this->request->getPost('tracking_number'));
        $courierName = trim((string) $this->request->getPost('courier_name'));

        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid order ID.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        $result = $this->orderService->updateTracking($id, $trackingNumber, $courierName);

        if (!$result['ok']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $result['message'],
                'token' => csrf_hash()
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $result['message'],
            'token' => csrf_hash(),
        ]);
    }

    public function refunds()
    {
        $refundModel = new RefundRequestModel();
        $rows = $refundModel->orderBy('created_at', 'DESC')->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function updateRefundStatus()
    {
        $id = (int) $this->request->getPost('id');
        $status = trim((string) $this->request->getPost('status'));
        if ($id <= 0 || ! in_array($status, ['Pending', 'Under Review', 'Approved', 'Rejected'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request.'])->setStatusCode(400);
        }

        $refundModel = new RefundRequestModel();
        $refund = $refundModel->find($id);
        if (! $refund) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Refund request not found.'])->setStatusCode(404);
        }

        if (! $refundModel->update($id, ['status' => $status])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update refund status.'])->setStatusCode(400);
        }

        if ($status === 'Approved') {
            $orderModel = new OrderModel();
            $orderModel->update((int) $refund['order_id'], ['status' => OrderModel::STATUS_REFUNDED]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Refund status updated.']);
    }

    abstract protected function getViewPrefix(): string;
}
