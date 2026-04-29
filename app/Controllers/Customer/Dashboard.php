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

        // 2. Fetch products and shippable locations
        $productModel = new ProductModel();
        $shippingModel = new \App\Models\ShippingLocationModel();

        // 3. Prepare data for the view
        $data =[
            'title'             => 'Customer Portal',
            'username'          => session()->get('username'),
            'products'          => $productModel->findAll(),
            'shippingLocations' => $shippingModel->where('is_active', 1)->findAll()
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
        $db = db_connect();

        // Generate a unique transaction code
        $transactionCode = 'ORD-' . strtoupper(uniqid());

        // Calculate total amount on the server-side to prevent tampering
        $serverCalculatedTotal = 0;
        $itemsToSave = [];
        $errors = [];

        foreach ($orderData['items'] as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($productId <= 0 || $quantity <= 0) {
                $errors[] = 'Invalid product or quantity detected in cart.';
                continue;
            }

            $product = $productModel->find($productId);
            if (!$product) {
                $errors[] = "Product '{$item['name']}' not found.";
                continue;
            }
            if ((float) $product['current_stock'] < $quantity) {
                $errors[] = "Not enough stock for '{$item['name']}'. Available: {$product['current_stock']}, Requested: {$quantity}.";
                continue;
            }

            $itemPrice = (float) $product['selling_price'];
            $itemSubtotal = $itemPrice * $quantity;
            $serverCalculatedTotal += $itemSubtotal;

            $itemsToSave[] = [
                'product_id'   => $productId,
                'product_name' => $product['name'],
                'unit'         => $product['unit'],
                'quantity'     => $quantity,
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

        $db->transBegin();
        try {
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
                throw new \RuntimeException('Failed to create order.');
            }

            // 7. Save order items and update stock atomically.
            foreach ($itemsToSave as $item) {
                $item['order_id'] = $orderId;
                if (! $orderItemModel->insert($item)) {
                    throw new \RuntimeException('Failed to save order line items.');
                }

                // Atomic decrement guarded by available stock.
                $builder = $db->table('products');
                $builder->set('current_stock', 'current_stock - ' . (int) $item['quantity'], false);
                $builder->where('id', (int) $item['product_id']);
                $builder->where('current_stock >=', (int) $item['quantity']);
                $builder->update();

                if ($db->affectedRows() !== 1) {
                    throw new \RuntimeException("Stock changed before checkout for {$item['product_name']}. Please try again.");
                }
            }

            // 8. Record sales history
            $salesModel = new \App\Models\SalesModel();
            if (! $salesModel->recordFromOrder($transactionCode, array_column($itemsToSave, 'product_name'), (float) $serverCalculatedTotal)) {
                throw new \RuntimeException('Failed to record sales history.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()])
                ->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order transaction failed.'])->setStatusCode(500);
        }

        $db->transCommit();

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
        $barangay = trim($this->request->getPost('barangay'));
        if (empty($barangay)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No location detected']);
        }

        $shippingModel = new \App\Models\ShippingLocationModel();
        
        // Simple search - MySQL is case-insensitive by default for VARCHAR
        $location = $shippingModel->where('barangay_name', $barangay)
                                 ->where('is_active', 1)
                                 ->first();

        // If not found, try a broader search just in case
        if (!$location) {
            $location = $shippingModel->like('barangay_name', $barangay)
                                     ->where('is_active', 1)
                                     ->first();
        }

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
        $orderItemModel = new \App\Models\OrderItemModel();
        $db = db_connect();

        $order = $orderModel->where('id', $orderId)
                           ->where('customer_name', session()->get('username'))
                           ->first();

        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found'])->setStatusCode(404);
        }

        if ($order['status'] !== 'Pending') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Only pending orders can be cancelled.'])->setStatusCode(400);
        }

        $db->transBegin();
        try {
            // Return stock before cancelling
            $items = $orderItemModel->where('order_id', $orderId)->findAll();
            foreach ($items as $item) {
                $builder = $db->table('products');
                $builder->set('current_stock', 'current_stock + ' . (int) $item['quantity'], false);
                $builder->where('id', (int) $item['product_id']);
                $builder->update();

                if ($db->affectedRows() !== 1) {
                    throw new \RuntimeException("Failed to restore stock for product ID {$item['product_id']}.");
                }
            }

            if (! $orderModel->update($orderId, ['status' => 'Cancelled'])) {
                throw new \RuntimeException('Failed to cancel order.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response
                ->setJSON(['status' => 'error', 'message' => $e->getMessage()])
                ->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cancellation transaction failed.'])->setStatusCode(500);
        }

        $db->transCommit();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Order cancelled successfully.']);
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