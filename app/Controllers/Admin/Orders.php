<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;

class Orders extends BaseController
{
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

        if (! in_array($status, [
            OrderModel::STATUS_PENDING,
            OrderModel::STATUS_PROCESSING,
            OrderModel::STATUS_SHIPPED,
            OrderModel::STATUS_COMPLETED,
            OrderModel::STATUS_CANCELLED,
            OrderModel::STATUS_REFUNDED
        ], true)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid order status.',
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        $model = new OrderModel();
        $order = $model->find($id);
        if (! $order) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Order not found',
                'token'   => csrf_hash(),
            ])->setStatusCode(404);
        }

        $db = db_connect();
        $db->transBegin();

        if ($model->update($id, ['status' => $status])) {
            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Failed to update status',
                    'token'   => csrf_hash(),
                ])->setStatusCode(500);
            }
            $db->transCommit();
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => 'Status updated successfully',
                'token' => csrf_hash()
            ]);
        }

        $db->transRollback();
        
        return $this->response->setJSON([
            'status' => 'error', 
            'message' => 'Failed to update status',
            'token' => csrf_hash()
        ])->setStatusCode(500);
    }
}