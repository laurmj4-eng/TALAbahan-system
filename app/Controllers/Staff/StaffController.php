<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\SalesModel;
use App\Models\UserModel;

class StaffController extends BaseController
{
    /**
     * Staff Dashboard - Main Overview
     */
    public function index()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login')->with('error', 'Access Denied: Staff Only');
        }

        $orderModel = new OrderModel();
        $productModel = new ProductModel();

        $data = [
            'title'    => 'Staff Dashboard',
            'username' => session()->get('username'),
            'cards'    => [
                'today_orders'     => 0,
                'total_products'   => 0,
                'low_stock_count'  => 0,
                'out_of_stock'     => 0,
            ],
        ];

        // Get today's order count
        $data['cards']['today_orders'] = (int) $orderModel
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        // Get product statistics
        $data['cards']['total_products'] = (int) $productModel->countAllResults();
        $data['cards']['low_stock_count'] = (int) $productModel->where('current_stock <=', 5)->countAllResults();
        $data['cards']['out_of_stock'] = (int) $productModel->where('current_stock <=', 0)->countAllResults();

        return view('staff/dashboard', $data);
    }

    /**
     * View All Products
     */
    public function products()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $data = [
            'title'    => 'Product Management - Staff',
            'username' => session()->get('username'),
            'products' => $productModel->getWithCategory(),
        ];

        return view('staff/products', $data);
    }

    /**
     * Get Products (JSON for modal/table)
     */
    public function getProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        return $this->response->setJSON($productModel->getWithCategory());
    }

    /**
     * Add New Product
     */
    public function addProduct()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['ok' => false, 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();

        $data = [
            'name'           => trim($this->request->getPost('name')),
            'cost_price'     => (float) $this->request->getPost('cost_price') ?? 0,
            'selling_price'  => (float) $this->request->getPost('selling_price') ?? 0,
            'initial_stock'  => (float) $this->request->getPost('initial_stock') ?? 0,
            'current_stock'  => (float) $this->request->getPost('current_stock') ?? 0,
            'wastage_qty'    => (float) $this->request->getPost('wastage_qty') ?? 0,
            'unit'           => trim($this->request->getPost('unit')) ?? 'piece',
        ];

        if (!$productModel->insert($data)) {
            return $this->response->setJSON([
                'ok'      => false,
                'message' => implode(', ', $productModel->errors())
            ]);
        }

        log_message('info', 'Staff ' . session()->get('username') . ' added product: ' . $data['name']);

        return $this->response->setJSON([
            'ok'      => true,
            'message' => 'Product added successfully!',
            'product_id' => $productModel->getInsertID()
        ]);
    }

    /**
     * Update Product Stock
     */
    public function updateStock()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['ok' => false, 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $productId = (int) $this->request->getPost('product_id');
        $newStock = (float) $this->request->getPost('current_stock');

        if ($productId <= 0 || $newStock < 0) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Invalid input']);
        }

        $product = $productModel->find($productId);
        if (!$product) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Product not found']);
        }

        if (!$productModel->update($productId, ['current_stock' => $newStock])) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Failed to update stock']);
        }

        log_message('info', 'Staff ' . session()->get('username') . ' updated stock for product ID ' . $productId);

        return $this->response->setJSON([
            'ok'      => true,
            'message' => 'Stock updated successfully!'
        ]);
    }

    /**
     * Update Product Details
     */
    public function updateProduct()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['ok' => false, 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $productId = (int) $this->request->getPost('id');

        $data = [
            'name'          => trim($this->request->getPost('name')),
            'cost_price'    => (float) $this->request->getPost('cost_price'),
            'selling_price' => (float) $this->request->getPost('selling_price'),
            'unit'          => trim($this->request->getPost('unit')),
        ];

        if (!$productModel->update($productId, $data)) {
            return $this->response->setJSON([
                'ok'      => false,
                'message' => implode(', ', $productModel->errors())
            ]);
        }

        log_message('info', 'Staff ' . session()->get('username') . ' updated product ID ' . $productId);

        return $this->response->setJSON([
            'ok'      => true,
            'message' => 'Product updated successfully!'
        ]);
    }

    /**
     * Get Orders Overview
     */
    public function orders()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $orderModel = new OrderModel();
        $data = [
            'title'    => 'Order Tracking - Staff',
            'username' => session()->get('username'),
            'orders'   => $orderModel->getOrdersWithItemCount(),
        ];

        return view('staff/orders', $data);
    }

    /**
     * Get Orders (JSON)
     */
    public function getOrders()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $page = (int) $this->request->getGet('page') ?? 1;
        
        $orders = $orderModel->getOrdersWithItemCount();
        return $this->response->setJSON($orders);
    }

    /**
     * Get Order Details
     */
    public function getOrderDetail($orderId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getOrderWithItems($orderId);

        if (!$order) {
            return $this->response->setJSON(['error' => 'Order not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($order);
    }

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

        return view('staff/sales_history', $data);
    }

    /**
     * Get Sales History (JSON)
     */
    public function getSalesHistory()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $salesModel = new SalesModel();
        $history = $salesModel->orderBy('created_at', 'DESC')->findAll();
        
        return $this->response->setJSON($history ?? []);
    }

    /**
     * Get Low Stock Products
     */
    public function getLowStockProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $lowStock = $productModel->getLowStockProducts(10);

        return $this->response->setJSON($lowStock);
    }

    /**
     * Get Best Sellers
     */
    public function getBestSellers()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $bestSellers = $productModel->getBestSellers(10);

        return $this->response->setJSON($bestSellers);
    }

    /**
     * Get Daily Inventory Summary
     */
    public function getInventorySummary()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $inventory = $productModel->getDailyInventory();

        return $this->response->setJSON($inventory);
    }
}