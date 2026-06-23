<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Traits\ApiResponseTrait;
use App\Controllers\Traits\OrderOperationsTrait;
use App\Models\OrderModel;

class Orders extends BaseController
{
    use ApiResponseTrait, OrderOperationsTrait;

    public function __construct()
    {
        $this->initializeOrderService();
    }

    public function index()
    {
        $model = new OrderModel();

        $model->select('orders.*, (SELECT COUNT(id) FROM order_items WHERE order_items.order_id = orders.id) as item_count');
        $data = [
            'orders' => $model->orderBy('created_at', 'DESC')->paginate(10),
            'pager'  => $model->pager,
        ];

        $data['username'] = session()->get('username');

        return inertia('admin/Orders', $data);
    }

    public function show($id)
    {
        return $this->getOrderDetail($id);
    }

    public function updateStatus()
    {
        return $this->updateOrderStatus();
    }

    public function cancelDamagedInTransit()
    {
        $json = $this->request->getJSON();
        if ($json) {
            $id = (int) ($json->id ?? 0);
            $issueRedeliveryRaw = $json->issue_redelivery ?? '0';
        } else {
            $id = (int) $this->request->getVar('id');
            $issueRedeliveryRaw = $this->request->getVar('issue_redelivery');
        }
        $issueRedelivery = ($issueRedeliveryRaw === '1' || $issueRedeliveryRaw === 1 || $issueRedeliveryRaw === true);

        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid data - missing order ID',
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        $recordedBy = session()->get('username') ?? 'Admin';
        $result = $this->orderService->cancelDamagedInTransit($id, $recordedBy, $issueRedelivery);

        if (!$result['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'],
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $result['message'],
            'token'   => csrf_hash(),
        ]);
    }

    protected function getViewPrefix(): string
    {
        return 'admin';
    }
}
