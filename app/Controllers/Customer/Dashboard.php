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

        // Server-side location re-validation
        $shippingDetails = $orderData['shipping_details'] ?? null;
        if (!$shippingDetails || empty($shippingDetails['barangay'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Shipping location required.'])->setStatusCode(400);
        }

        $shippingModel = new \App\Models\ShippingLocationModel();
        $isShippable = $shippingModel->where('barangay_name', $shippingDetails['barangay'])->where('is_active', 1)->first();
        if (!$isShippable) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Sorry, we do not ship to this location.'])->setStatusCode(400);
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

        // 5. Handle Mock GCash API Simulation
        $paymentMethod = $orderData['payment_method'] ?? 'COD';
        if ($paymentMethod === 'GCash') {
            $paymentResult = $this->simulateGcashPayment($serverCalculatedTotal, $transactionCode);
            if (!$paymentResult['success']) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'GCash Payment Failed: ' . $paymentResult['error']])->setStatusCode(400);
            }
        }

        // 6. Create the main order
        $orderId = $orderModel->insert([
            'transaction_code' => $transactionCode,
            'customer_name'    => $shippingDetails['name'] ?? session()->get('username'),
            'total_amount'     => $serverCalculatedTotal,
            'status'           => 'Pending',
            'notes'            => 'Customer online order',
            'payment_method'   => $paymentMethod,
            'shipping_barangay' => $shippingDetails['barangay'],
            'shipping_phone'    => $shippingDetails['phone']
        ]);

        if (!$orderId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create order.'])->setStatusCode(500);
        }

        // 7. Save order items and update product stock
        foreach ($itemsToSave as $item) {
            $item['order_id'] = $orderId;
            $orderItemModel->insert($item);

            // Deduct stock
            $productModel->update($item['product_id'], ['current_stock' => new \CodeIgniter\Database\RawSql('current_stock - ' . $item['quantity'])]);
        }

        // 8. Record sales history (optional, if different from orders)
        $salesModel = new \App\Models\SalesModel();
        $salesModel->recordFromOrder($transactionCode, array_column($itemsToSave, 'product_name'), $serverCalculatedTotal);

        return $this->response->setJSON([
            'status' => 'success', 
            'message' => ($paymentMethod === 'GCash' ? 'GCash Payment Successful! ' : '') . 'Order placed!', 
            'transaction_code' => $transactionCode
        ]);
    }

    /**
     * Validate if the detected barangay is shippable
     */
    public function validateLocation()
    {
        $barangay = $this->request->getPost('barangay');
        if (empty($barangay)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No location detected']);
        }

        $shippingModel = new \App\Models\ShippingLocationModel();
        
        // Search for active barangay matching the name
        $location = $shippingModel->where('barangay_name', $barangay)
                                 ->where('is_active', 1)
                                 ->first();

        if ($location) {
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Location not supported']);
    }

    /**
     * Fetch order details for the modal
     */
    public function orderDetails($orderId)
    {
        if (session()->get('role') !== 'customer') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderModel = new \App\Models\OrderModel();
        $orderItemModel = new \App\Models\OrderItemModel();

        $order = $orderModel->where('id', $orderId)
                           ->where('customer_name', session()->get('username'))
                           ->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        $items = $orderItemModel->where('order_id', $orderId)->findAll();
        $order['items'] = $items;

        return $this->response->setJSON(['status' => 'success', 'data' => $order]);
    }

    /**
     * Cancel a pending order
     */
    public function cancelOrder()
    {
        if (session()->get('role') !== 'customer' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderId = $this->request->getPost('id');
        $orderModel = new \App\Models\OrderModel();
        $productModel = new \App\Models\ProductModel();
        $orderItemModel = new \App\Models\OrderItemModel();

        $order = $orderModel->where('id', $orderId)
                           ->where('customer_name', session()->get('username'))
                           ->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        if ($order['status'] !== 'Pending') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Only pending orders can be cancelled.'])->setStatusCode(400);
        }

        // Return stock before cancelling
        $items = $orderItemModel->where('order_id', $orderId)->findAll();
        foreach ($items as $item) {
            $productModel->update($item['product_id'], [
                'current_stock' => new \CodeIgniter\Database\RawSql('current_stock + ' . $item['quantity'])
            ]);
        }

        if ($orderModel->update($orderId, ['status' => 'Cancelled'])) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to cancel order.'])->setStatusCode(500);
    }

    /**
     * Temporary Mock GCash API Simulation
     */
    private function simulateGcashPayment($amount, $refCode)
    {
        // Simulate external API call delay
        usleep(1500000); // 1.5 seconds

        // Simulate success for now
        return [
            'success' => true,
            'transaction_id' => 'GC-' . strtoupper(bin2hex(random_bytes(4))),
            'amount' => $amount,
            'ref_code' => $refCode
        ];
    }
}