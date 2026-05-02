<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CodComplianceModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
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
        $model = new OrderModel();
        $data['orders'] = $model->getOrdersWithItemCount();
        return view('admin/orders_standalone', $data);
    }

    public function show($id)
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
        $order['payments'] = [];
        $order['delivery'] = null;
        
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
        $db = db_connect();

        $rows = $db->table('order_items oi')
            ->select('oi.*, o.transaction_code, o.customer_name, o.status, o.created_at')
            ->join('orders o', 'o.id = oi.order_id', 'left')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('admin/order_items_view', ['orderItems' => $rows]);
    }

    /**
     * Lightweight endpoint for order line items only.
     */
    public function items($orderId)
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->find($orderId);

        if (! $order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found']);
        }

        $orderItemModel = new OrderItemModel();
        $items          = $orderItemModel->getItemsByOrder($orderId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'order_id'   => (int) $orderId,
                'items'      => $items,
                'item_count' => count($items),
            ],
        ]);
    }

    public function updateStatus()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access denied.',
                'token'   => csrf_hash(),
            ])->setStatusCode(403);
        }

        $id     = (int) $this->request->getPost('id');
        $status = trim((string) $this->request->getPost('status'));
        
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
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
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
        if (session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied'])->setStatusCode(403);
        }

        $refundModel = new RefundRequestModel();
        $rows = $refundModel->orderBy('created_at', 'DESC')->findAll();
        return $this->response->setJSON(['status' => 'success', 'data' => $rows]);
    }

    public function updateRefundStatus()
    {
        if (session()->get('role') !== 'admin' || ! $this->request->isAJAX()) {
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

        return $this->response->setJSON(['status' => 'success', 'message' => 'Refund status updated.']);
    }
}