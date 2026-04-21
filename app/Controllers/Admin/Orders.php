<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PaymentModel;
use App\Models\DeliveryModel;

class Orders extends BaseController
{
    public function index()
    {
        $model = new OrderModel();
        $data['orders'] = $model->getOrdersWithItemCount();
        return view('admin/orderview', $data);
    }

    public function show($id)
    {
        $orderModel = new OrderModel();
        $order      = $orderModel->find($id);
        
        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found']);
        }

        $orderItemModel = new OrderItemModel();
        $order['items'] = $orderItemModel->getItemsByOrder($id);
        $db = db_connect();
        if ($db->tableExists('payments')) {
            $paymentModel      = new PaymentModel();
            $order['payments'] = $paymentModel->where('order_id', $id)->orderBy('created_at', 'DESC')->findAll();
        } else {
            $order['payments'] = [];
        }

        if ($db->tableExists('deliveries')) {
            $deliveryModel     = new DeliveryModel();
            $order['delivery'] = $deliveryModel->where('order_id', $id)->orderBy('created_at', 'DESC')->first();
        } else {
            $order['delivery'] = null;
        }
        
        return $this->response->setJSON(['status' => 'success', 'data' => $order]);
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
        $id     = (int) $this->request->getPost('id');
        $status = trim((string) $this->request->getPost('status'));
        
        if (!$id || !$status) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);
        }

        if (! in_array($status, [OrderModel::STATUS_PENDING, OrderModel::STATUS_COMPLETED], true)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Status must be Pending or Completed only.',
            ]);
        }

        $model = new OrderModel();
        if ($model->update($id, ['status' => $status])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Status updated successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update status']);
    }
}