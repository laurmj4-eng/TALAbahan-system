<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\OrderModel;

class PosController extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $userModel = new \App\Models\UserModel();
        
        $data = [
            'title'     => 'TALAbahan Terminal (Staff)',
            'username'  => session()->get('username'),
            'products'  => $productModel->findAll(),
            'customers' => $userModel->where('role', 'customer')->findAll(),
        ];

        return inertia('staff/Pos', $data);
    }

    // 1. Get products for the POS screen
    public function getProducts()
    {
        if (!in_array(session()->get('role'), ['admin', 'staff'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $model = new ProductModel();
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Products fetched.',
            'data'    => $model->findAll(),
            'token'   => csrf_hash(),
        ]);
    }

    // 2. Process checkout — writes to orders, order_items, AND sales_history
    public function checkout()
    {
        if (!in_array(session()->get('role'), ['admin', 'staff'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        // Try to get data from POST first (FormData), then fall back to JSON
        $itemsRaw = $this->request->getPost('items');
        if ($itemsRaw) {
            $items = json_decode($itemsRaw, true);
            $customerName = $this->request->getPost('customer_name');
            $customerAlias = $this->request->getPost('customer_alias');
            $userId = $this->request->getPost('user_id');
            $voucherCode = $this->request->getPost('voucher_code');
            $payload = [
                'items' => $items,
                'customer_name' => $customerName,
                'customer_alias' => $customerAlias,
                'user_id' => $userId,
                'voucher_code' => $voucherCode
            ];
        } else {
            $payload = $this->request->getJSON(true);
        }

        if (!$payload || empty($payload['items'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.', 'token' => csrf_hash()])->setStatusCode(400);
        }

        $orderModel = new OrderModel();
        $payload['moved_by'] = (int) (session()->get('user_id') ?? 0);
        $result     = $orderModel->createFromCheckout($payload);
        if (! $result['ok']) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $result['message'],
                'token'   => csrf_hash(),
            ])->setStatusCode(400);
        }

        return $this->response->setJSON([
            'status'  => 'success', 
            'message' => 'Payment of ₱' . number_format((float) $result['total_amount'], 2)
                . ' processed! (TXN: ' . $result['transaction_code'] . ')',
            'data'    => [
                'transaction_code' => $result['transaction_code'],
                'total_amount'     => (float) $result['total_amount'],
                'discount'         => (float) ($result['discount'] ?? 0),
                'customer'         => $result['customer'] ?? 'Walk-in Customer',
                'items'            => $result['items'] ?? [],
                'date'             => $result['date'] ?? date('Y-m-d H:i:s'),
            ],
            'token'   => csrf_hash(),
        ]);
    }
}
