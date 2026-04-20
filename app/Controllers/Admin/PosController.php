<?php

namespace App\Controllers\Admin; // Updated namespace for the Admin subfolder

use App\Controllers\BaseController; // Required to find BaseController
use App\Models\ProductModel;
use App\Models\SalesModel;

class PosController extends BaseController
{
    // 1. Get products for the POS screen
    public function getProducts()
    {
        $model = new ProductModel();
        return $this->response->setJSON($model->findAll());
    }

    // 2. Process checkout and SAVE to Sales History
    public function checkout()
    {
        $json = $this->request->getJSON();
        
        if (!$json || empty($json->items)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.']);
        }

        $productModel = new ProductModel();
        $salesModel = new SalesModel();
        
        $itemNames = []; // To store a summary of items bought

        // Deduct stock
        foreach ($json->items as $item) {
            $product = $productModel->find($item->id);
            if ($product) {
                // Note: Ensure your DB column is 'stock' or 'current_stock' as per your Model
                $newStock = $product['stock'] - $item->qty;
                $productModel->update($item->id, ['stock' => $newStock]);
            }
            $itemNames[] = $item->qty . 'x ' . $item->name;
        }

        // Save to Sales History
        $transactionCode = 'TXN-' . strtoupper(substr(uniqid(), -6));
        
        $salesModel->insert([
            'transaction_code' => $transactionCode,
            'items_summary'    => implode(', ', $itemNames), // e.g., "2x Baked Talaba, 1x Crab"
            'total_amount'     => $json->total,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => "Payment of ₱" . number_format($json->total, 2) . " processed! (TXN: $transactionCode)"
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