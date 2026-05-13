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
        $data = [
            'title'    => 'Sales History - Staff',
            'username' => session()->get('username'),
            'sales'    => $salesModel->orderBy('created_at', 'DESC')->findAll(),
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
        $history = $salesModel->orderBy('created_at', 'DESC')->findAll();
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Sales history fetched.', 'data' => $history ?? [], 'token' => csrf_hash()]);
    }
}
