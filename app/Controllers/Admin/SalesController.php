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
        // Join with orders to get customer details
        $sales = $salesModel->db->table('sales_history s')
            ->select('s.*, o.customer_name, o.customer_alias, o.user_id')
            ->join('orders o', 'o.transaction_code = s.transaction_code', 'left')
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'title'    => 'Financial Ledger',
            'username' => session()->get('username'),
            'sales'    => $sales,
        ];

        return inertia('admin/SalesHistory', $data);
    }
}
