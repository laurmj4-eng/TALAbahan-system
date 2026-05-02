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
     * View All Products (Staff - View Only)
     */
    public function products()
    {
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login');
        }

        $productModel = new ProductModel();
        $data = [
            'title'    => 'Product Inventory - Staff',
            'username' => session()->get('username'),
            'products' => $productModel->getWithCategory(),
        ];

        return view('staff/products', $data);
    }

    /**
     * Get Products (JSON for table)
     */
    public function getProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Products fetched.', 'data' => $productModel->getWithCategory(), 'token' => csrf_hash()]);
    }

    /**
     * Get Product Details (JSON)
     */
    public function getDetails($productId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Product not found', 'token' => csrf_hash()])->setStatusCode(404);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Product fetched.', 'data' => $product, 'token' => csrf_hash()]);
    }

    /* 
     * CRUD ACTIONS REMOVED FOR STAFF ROLE:
     * addProduct(), updateStock(), updateProduct()
     * Staff can only view products and their current stock levels.
     */

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
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access Denied',
                'token'   => csrf_hash()
            ]);
        }

        try {
            $orderModel = new OrderModel();
            $orderId    = (int) $this->request->getPost('id');
            $newStatus  = trim((string) $this->request->getPost('status'));

            if (empty($orderId) || empty($newStatus)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Missing required data',
                    'token'   => csrf_hash()
                ])->setStatusCode(400);
            }

            $allowedStatuses = [
                OrderModel::STATUS_PENDING,
                OrderModel::STATUS_PROCESSING,
                OrderModel::STATUS_SHIPPED,
                OrderModel::STATUS_COMPLETED,
                OrderModel::STATUS_CANCELLED,
                OrderModel::STATUS_REFUNDED,
            ];
            if (! in_array($newStatus, $allowedStatuses, true)) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid order status.',
                    'token'   => csrf_hash(),
                ])->setStatusCode(400);
            }

            $order = $orderModel->find($orderId);
            if (!$order) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Order not found.',
                    'token'   => csrf_hash()
                ])->setStatusCode(404);
            }

            $db = db_connect();
            $db->transBegin();

            if (!$orderModel->update($orderId, ['status' => $newStatus])) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Database update failed: ' . implode(', ', $orderModel->errors()),
                    'token'   => csrf_hash()
                ])->setStatusCode(500);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Database update failed.',
                    'token'   => csrf_hash(),
                ])->setStatusCode(500);
            }
            $db->transCommit();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Order status updated to ' . $newStatus,
                'token'   => csrf_hash()
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'System error: ' . $e->getMessage(),
                'token'   => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    /**
     * Get Orders (JSON)
     */
    public function getOrders()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $orderModel = new OrderModel();
        $orders = $orderModel->getOrdersWithItemCount();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Orders fetched.', 'data' => $orders, 'token' => csrf_hash()]);
    }

    /**
     * Get Order Details
     */
    public function getOrderDetail($orderId)
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Access Denied',
                'token'   => csrf_hash()
            ]);
        }

        $orderModel = new OrderModel();
        $order      = $orderModel->find($orderId);

        if (!$order) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Order not found',
                'token'   => csrf_hash()
            ]);
        }

        $orderItemModel = new OrderItemModel();
        $order['items'] = $orderItemModel->getItemsByOrder($orderId);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $order,
            'token'  => csrf_hash()
        ]);
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
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $salesModel = new SalesModel();
        $history = $salesModel->orderBy('created_at', 'DESC')->findAll();
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Sales history fetched.', 'data' => $history ?? [], 'token' => csrf_hash()]);
    }

    /**
     * Get Low Stock Products
     */
    public function getLowStockProducts()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $lowStock = $productModel->getLowStockProducts(10);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Low-stock products fetched.', 'data' => $lowStock, 'token' => csrf_hash()]);
    }

    /**
     * Get Best Sellers
     */
    public function getBestSellers()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $bestSellers = $productModel->getBestSellers(10);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Best sellers fetched.', 'data' => $bestSellers, 'token' => csrf_hash()]);
    }

    /**
     * Get Daily Inventory Summary
     */
    public function getInventorySummary()
    {
        if (session()->get('role') !== 'staff') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access denied', 'token' => csrf_hash()])->setStatusCode(403);
        }

        $productModel = new ProductModel();
        $inventory = $productModel->getDailyInventory();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Inventory summary fetched.', 'data' => $inventory, 'token' => csrf_hash()]);
    }
}