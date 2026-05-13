<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SalesModel;

class SalesController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $salesModel = new SalesModel();
        $data = [
            'title'    => 'Financial Ledger',
            'username' => session()->get('username'),
            'sales'    => $salesModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return inertia('admin/SalesHistory', $data);
    }
}
