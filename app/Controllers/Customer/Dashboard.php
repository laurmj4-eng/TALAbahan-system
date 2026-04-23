<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ProductModel; // IMPORTANT: Add this to fetch products!

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch products from the database
        $productModel = new ProductModel();

        // 3. Prepare data for the view
        $data =[
            'title'    => 'Customer Portal',
            'username' => session()->get('username'),
            'products' => $productModel->findAll() // Sends products to the view
        ];

        // 4. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }

    public function orderItems()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $orderModel = new \App\Models\OrderModel();
        
        // Fetch only orders for this customer
        $orders = $orderModel->where('customer_name', session()->get('username'))
                            ->orderBy('created_at', 'DESC')
                            ->findAll();

        // 3. Prepare data for the view
        $data =[
            'title'    => 'My Orders',
            'username' => session()->get('username'),
            'orders'   => $orders
        ];

        // 4. Load the customer order items view (now as My Orders)
        return view('customer/order_items', $data);
    }

    public function placeOrder()
    {
        if (session()->get('role') !== 'customer' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderDataJson = $this->request->getPost('order_data');
        $orderData = json_decode($orderDataJson, true);

        if (empty($orderData['items'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cart is empty.'])->setStatusCode(400);
        }

        $orderModel = new \App\Models\OrderModel();
        $orderItemModel = new \App\Models\OrderItemModel();
        $productModel = new \App\Models\ProductModel();

        // Generate a unique transaction code
        $transactionCode = 'ORD-' . strtoupper(uniqid());

        // Calculate total amount on the server-side to prevent tampering
        $serverCalculatedTotal = 0;
        $itemsToSave = [];
        $errors = [];

        foreach ($orderData['items'] as $item) {
            $product = $productModel->find($item['id']);
            if (!$product) {
                $errors[] = "Product '{$item['name']}' not found.";
                continue;
            }
            if ($product['current_stock'] < $item['quantity']) {
                $errors[] = "Not enough stock for '{$item['name']}'. Available: {$product['current_stock']}, Requested: {$item['quantity']}.";
                continue;
            }

            $itemPrice = $product['selling_price'];
            $itemSubtotal = $itemPrice * $item['quantity'];
            $serverCalculatedTotal += $itemSubtotal;

            $itemsToSave[] = [
                'product_id'   => $item['id'],
                'product_name' => $product['name'],
                'unit'         => $product['unit'],
                'quantity'     => $item['quantity'],
                'unit_price'   => $itemPrice,
                'subtotal'     => $itemSubtotal,
            ];
        }

        if (!empty($errors)) {
            return $this->response->setJSON(['status' => 'error', 'message' => implode(' ', $errors)])->setStatusCode(400);
        }

        // Create the main order
        $orderId = $orderModel->insert([
            'transaction_code' => $transactionCode,
            'customer_name'    => session()->get('username'), // Or actual customer name from user model
            'total_amount'     => $serverCalculatedTotal,
            'status'           => 'Pending',
            'notes'            => 'Customer online order',
            'payment_method'   => $orderData['payment_method'] ?? 'COD',
        ]);

        if (!$orderId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create order.'])->setStatusCode(500);
        }

        // Save order items and update product stock
        foreach ($itemsToSave as $item) {
            $item['order_id'] = $orderId;
            $orderItemModel->insert($item);

            // Deduct stock
            $productModel->update($item['product_id'], ['current_stock' => new \CodeIgniter\Database\RawSql('current_stock - ' . $item['quantity'])]);
        }

        // Record sales history (optional, if different from orders)
        $salesModel = new \App\Models\SalesModel();
        $salesModel->recordFromOrder($transactionCode, array_column($itemsToSave, 'product_name'), $serverCalculatedTotal);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Order placed!', 'transaction_code' => $transactionCode]);
    }
}