<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\SalesModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;

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
        $json = $this->request->getJSON();
        
        if (!$json || empty($json->items)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.']);
        }

        $productModel   = new ProductModel();
        $salesModel     = new SalesModel();
        $orderModel     = new OrderModel();
        $orderItemModel = new OrderItemModel();
        
        $transactionCode = 'TXN-' . strtoupper(substr(uniqid(), -6));
        $itemNames = [];

        // --- A. Create the Order header ---
        $orderModel->insert([
            'transaction_code' => $transactionCode,
            'customer_name'    => $json->customer_name ?? 'Walk-in Customer',
            'total_amount'     => $json->total,
            'status'           => 'Completed',
        ]);
        $orderId = $orderModel->getInsertID();

        // --- B. Loop items: create line items + deduct stock ---
        foreach ($json->items as $item) {
            $product = $productModel->find($item->id);

            if ($product) {
                // Snapshot the price at time of sale (data integrity)
                $unitPrice = $product['selling_price'];
                $subtotal  = $unitPrice * $item->qty;

                // Insert order line item
                $orderItemModel->insert([
                    'order_id'     => $orderId,
                    'product_id'   => $item->id,
                    'product_name' => $product['name'],
                    'unit'         => $product['unit'] ?? 'piece',
                    'quantity'     => $item->qty,
                    'unit_price'   => $unitPrice,
                    'subtotal'     => $subtotal,
                ]);

                // Deduct stock (FIXED: was using 'stock', now uses 'current_stock')
                $newStock = $product['current_stock'] - $item->qty;
                $productModel->update($item->id, ['current_stock' => max(0, $newStock)]);
            }

            $itemNames[] = $item->qty . 'x ' . ($product['name'] ?? $item->name);
        }

        // --- C. Also write to sales_history for backward compatibility ---
        $salesModel->insert([
            'transaction_code' => $transactionCode,
            'items_summary'    => implode(', ', $itemNames),
            'total_amount'     => $json->total,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status'  => 'success', 
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