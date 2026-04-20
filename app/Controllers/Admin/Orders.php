<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;

class Orders extends BaseController
{
    public function index()
    {
        $model = new OrderModel();
        $data['orders'] = $model->orderBy('created_at', 'DESC')->findAll();
        return view('admin/orderview', $data);
    }

    public function store()
    {
        // No need to check for login here! The Filter already blocked hackers.
        $model = new OrderModel();
        $model->save([
            'customer_name' => $this->request->getPost('customer_name'),
            'total_amount'  => $this->request->getPost('total_amount'),
            'status'        => $this->request->getPost('status'),
        ]);
        return redirect()->to('/admin/orders');
    }
}