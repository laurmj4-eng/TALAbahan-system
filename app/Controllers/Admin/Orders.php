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
        // Uses the new method we added to get item count
        $data['orders'] = $model->getOrdersWithItemCount();
        return view('admin/orderview', $data);
    }

    public function show($id)
    {
        $model = new OrderModel();
        $order = $model->getOrderWithItems($id);
        
        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found']);
        }
        
        return $this->response->setJSON(['status' => 'success', 'data' => $order]);
    }

    public function updateStatus()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        
        if (!$id || !$status) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid data']);
        }

        $model = new OrderModel();
        if ($model->update($id, ['status' => $status])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Status updated successfully']);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update status']);
    }
}