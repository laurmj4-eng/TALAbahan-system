<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\CodComplianceModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusHistoryModel;
use App\Models\RefundRequestModel;
use App\Services\OrderService;

class Orders extends BaseController
{
    protected $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function index()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $model = new OrderModel();
        
        // Add pagination: 10 orders per page
        $data = [
            'orders' => $model->orderBy('created_at', 'DESC')->paginate(10),
            'pager'  => $model->pager,
            'title'  => 'Order Tracking - Staff',
            'username' => session()->get('username'),
        ];

        // We need to inject the item count for each order
        foreach ($data['orders'] as &$o) {
            $db = db_connect();
            $o['item_count'] = $db->table('order_items')->where('order_id', $o['id'])->countAllResults();
        }

        return inertia('staff/Orders', $data);
    }

    /**
     * Get Orders (JSON) for SPA
     */
    public function getOrders()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $orders = $orderModel->getOrdersWithItemCount();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Orders fetched.', 'data' => $orders, 'token' => csrf_hash()]);
    }

    public function getOrderDetail($id)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

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

    /**
     * UI page for all order line items.
     */
    public function itemsPage()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $db = db_connect();

        $rows = $db->table('order_items oi')
            ->select('oi.*, o.transaction_code, o.customer_name, o.status, o.created_at')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return inertia('staff/OrderItems', [
            'orderItems' => $rows,
            'username' => session()->get('username')
        ]);
    }

    /**
     * Lightweight endpoint for order line items only.
     */
    public function items($orderId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

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
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access denied.',
                'token'   => csrf_hash(),
            ])->setStatusCode(403);
        }

        $id     = (int) $this->request->getPost('id');
        $status = trim((string) $this->request->getPost('status'));
        
        if (!$id || !$status) {
            // Check for JSON payload if not in POST
            $json = $this->request->getJSON();
            if ($json) {
                $id = $json->id ?? 0;
                $status = $json->status ?? '';
            }
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
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied.'])->setStatusCode(403);
        }

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
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $refundModel = new RefundRequestModel();
        $rows = $refundModel->orderBy('created_at', 'DESC')->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function updateRefundStatus()
    {
        if (session()->get('role') !== 'staff' || ! $this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

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

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Refund status updated to ' . $status,
            'token' => csrf_hash()
        ]);
    }
}
