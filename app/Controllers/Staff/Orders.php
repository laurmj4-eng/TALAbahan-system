<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Controllers\Traits\OrderOperationsTrait;
use App\Models\OrderModel;

class Orders extends BaseController
{
    use OrderOperationsTrait;

    public function __construct()
    {
        $this->initializeOrderService();
    }

    public function index()
    {
        $model = new OrderModel();

        $data = [
            'orders' => $model->orderBy('created_at', 'DESC')->paginate(10),
            'pager'  => $model->pager,
            'title'  => 'Order Tracking - Staff',
            'username' => session()->get('username'),
        ];

        foreach ($data['orders'] as &$o) {
            $db = db_connect();
            $o['item_count'] = $db->table('order_items')->where('order_id', $o['id'])->countAllResults();
        }

        return inertia('staff/Orders', $data);
    }

    public function getOrders()
    {
        $orderModel = new OrderModel();
        $orders = $orderModel->getOrdersWithItemCount();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Orders fetched.', 'data' => $orders, 'token' => csrf_hash()]);
    }

    protected function getViewPrefix(): string
    {
        return 'staff';
    }
}
