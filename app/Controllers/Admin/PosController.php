<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\OrderModel;

class PosController extends BaseController
{
    // 1. Get products for the POS screen
    public function getProducts()
    {
        $model = new ProductModel();
        return $this->response->setJSON($model->findAll());
    }

    // 2. Process checkout — writes to orders, order_items, AND sales_history
    public function checkout()
    {
        $json = $this->request->getJSON(true);

        if (!$json || empty($json['items'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.']);
        }

        $orderModel = new OrderModel();
        $json['moved_by'] = (int) (session()->get('user_id') ?? 0);
        $result     = $orderModel->createFromCheckout($json);
        if (! $result['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'],
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success', 
            'message' => 'Payment of ₱' . number_format((float) $result['total_amount'], 2)
                . ' processed! (TXN: ' . $result['transaction_code'] . ')'
        ]);
    }

    // 3. Fetch Sales History for the Dashboard
    public function getHistory()
    {
        $salesModel = new SalesModel();
        $history = $salesModel->orderBy('created_at', 'DESC')->findAll();
        
        return $this->response->setJSON($history);
    }
}