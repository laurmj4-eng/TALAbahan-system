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
            'chart'    => [
                'labels' => [],
                'sales'  => [],
            ],
        ];

        // Get today's order count
        $today = date('Y-m-d');
        $data['cards']['today_orders'] = (int) $orderModel
            ->where('DATE(created_at)', $today)
            ->countAllResults();

        // Get product statistics
        $data['cards']['total_products'] = (int) $productModel->countAllResults();
        $data['cards']['low_stock_count'] = (int) $productModel->where('current_stock <=', 5)->countAllResults();
        $data['cards']['out_of_stock'] = (int) $productModel->where('current_stock <=', 0)->countAllResults();

        // Get 7-day sales trend for chart
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
        $salesTrend = $orderModel
            ->select('DATE(created_at) as order_date, SUM(total_amount) as daily_sales')
            ->where('DATE(created_at) >=', $sevenDaysAgo)
            ->groupBy('order_date')
            ->orderBy('order_date', 'ASC')
            ->findAll();

        $chartLabels = [];
        $chartSales = [];
        $period = new \DatePeriod(
            new \DateTime($sevenDaysAgo),
            new \DateInterval('P1D'),
            new \DateTime($today . ' +1 day')
        );

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
            $dailySales = 0;
            foreach ($salesTrend as $trend) {
                if ($trend['order_date'] === $dateStr) {
                    $dailySales = (float)$trend['daily_sales'];
                    break;
                }
            }
            $chartSales[] = $dailySales;
        }

        $data['chart']['labels'] = $chartLabels;
        $data['chart']['sales'] = $chartSales;

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
     * Get Product Details (JSON)
     */
    public function getDetails($productId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['error' => 'Access Denied'])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['error' => 'Product not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($product);
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

        $img = $this->request->getFile('image');
        $imageName = null;
        if ($img && $img->isValid() && ! $img->hasMoved()) {
            $imageName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/products', $imageName);
        }

        $data = [
            'name'           => trim($this->request->getPost('name')),
            'cost_price'     => (float) $this->request->getPost('cost_price') ?? 0,
            'selling_price'  => (float) $this->request->getPost('selling_price') ?? 0,
            'initial_stock'  => (float) $this->request->getPost('initial_stock') ?? 0,
            'current_stock'  => (float) $this->request->getPost('current_stock') ?? 0,
            'wastage_qty'    => (float) $this->request->getPost('wastage_qty') ?? 0,
            'unit'           => trim($this->request->getPost('unit')) ?? 'piece',
            'image'          => $imageName,
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
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Product not found'])->setStatusCode(404);
        }

        $img = $this->request->getFile('image');
        $imageName = $product['image'];

        if ($img && $img->isValid() && ! $img->hasMoved()) {
            // Delete old image if exists
            if ($imageName && file_exists(ROOTPATH . 'public/uploads/products/' . $imageName)) {
                unlink(ROOTPATH . 'public/uploads/products/' . $imageName);
            }
            $imageName = $img->getRandomName();
            $img->move(ROOTPATH . 'public/uploads/products', $imageName);
        }

        $data = [
            'name'          => trim($this->request->getPost('name')),
            'cost_price'    => (float) $this->request->getPost('cost_price'),
            'selling_price' => (float) $this->request->getPost('selling_price'),
            'unit'          => trim($this->request->getPost('unit')),
            'image'         => $imageName,
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
     * Update Order Status (Staff)
     */
    public function updateOrderStatus()
    {
        if (session()->get('role') !== 'staff' || !$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $orderId = $this->request->getPost('id');
        $newStatus = $this->request->getPost('status');

        $order = $orderModel->find($orderId);
        if (!$order) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Order not found.'])->setStatusCode(404);
        }

        if (!$orderModel->update($orderId, ['status' => $newStatus])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update order status.'])->setStatusCode(500);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Order status updated.']);
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