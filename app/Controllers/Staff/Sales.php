<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\SalesModel;

class Sales extends BaseController
{
    /**
     * View Sales History
     */
    public function salesHistory()
    {
        if (session()->get('role') !== 'staff') {
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
            'title'    => 'Sales History - Staff',
            'username' => session()->get('username'),
            'sales'    => $sales,
        ];

        return inertia('staff/SalesHistory', $data);
    }

    /**
     * Get Sales History (JSON)
     */
    public function getSalesHistory()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $salesModel = new SalesModel();
        $history = $salesModel->db->table('sales_history s')
            ->select('s.*, o.customer_name, o.customer_alias, o.user_id')
            ->join('orders o', 'o.transaction_code = s.transaction_code', 'left')
            ->orderBy('s.created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Sales history fetched.', 'data' => $history ?? [], 'token' => csrf_hash()]);
    }
}
