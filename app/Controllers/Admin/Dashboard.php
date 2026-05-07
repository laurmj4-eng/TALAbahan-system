<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\SalesModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        
        // Ensure DB connection uses the same timezone as PHP (Asia/Manila)
        $db = \Config\Database::connect();
        $db->query("SET time_zone = '+08:00'");
        
        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'users'    => $userModel->findAll(),
            'cards'    => [
                'today_sales'      => 0,
                'today_orders'     => 0,
                'today_profit'     => 0,
                'profit_margin'    => 0,
            ],
            'chart'    => [
                'labels'   => [],
                'dates'    => [],
                'sales'    => [],
            ],
        ];

        // Last 7-day trend chart for owner-friendly monitoring.
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} day"));
            $data['chart']['dates'][] = $date;
            $data['chart']['labels'][] = date('M d', strtotime($date));
            $data['chart']['sales'][] = 0;
        }

        $orderModel = new OrderModel();
        foreach ($data['chart']['dates'] as $idx => $date) {
            $data['chart']['sales'][$idx] = round($orderModel->getDailyRevenue($date), 2);
        }

        $data['cards']['today_sales'] = round((float) $orderModel->getTodayRevenue(), 2);
        $data['cards']['today_profit'] = round((float) $orderModel->getTodayProfit(), 2);
        
        if ($data['cards']['today_sales'] > 0) {
            $data['cards']['profit_margin'] = round(($data['cards']['today_profit'] / $data['cards']['today_sales']) * 100, 1);
        }
        
        // --- ADDED LEDGER DATA FETCHING ---
        try {
            $salesModel = new SalesModel();
            $data['ledger_history'] = $salesModel->orderBy('created_at', 'DESC')->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Ledger Fetch Error: ' . $e->getMessage());
            $data['ledger_history'] = [];
        }
        // ----------------------------------

        // Calculate Growth Metric (Today vs Yesterday)
        $yesterdaySales = round($orderModel->getDailyRevenue(date('Y-m-d', strtotime('-1 day'))), 2);
        $growth = 0;
        if ($yesterdaySales > 0) {
            $growth = (($data['cards']['today_sales'] - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($data['cards']['today_sales'] > 0) {
            $growth = 100; // 100% growth if yesterday was 0
        }
        $data['cards']['sales_growth'] = round($growth, 1);

        $today = date('Y-m-d');
        $data['cards']['today_orders'] = (int) $orderModel
            ->groupStart()
                ->where('DATE(created_at)', $today)
                ->orWhere("created_at LIKE '{$today}%'")
            ->groupEnd()
            ->countAllResults();

        // --- ORDER AGING ALERTS (Stale Orders > 24 Hours) ---
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $data['stale_orders'] = $orderModel
            ->whereIn('status', [OrderModel::STATUS_PENDING, OrderModel::STATUS_PROCESSING])
            ->where('created_at <', $yesterday)
            ->orderBy('created_at', 'ASC')
            ->findAll();
        
        $data['cards']['stale_orders_count'] = count($data['stale_orders']);

        // Top 3 Selling Products (Last 30 Days)
        $db = \Config\Database::connect();
        $data['top_products'] = $db->table('order_items oi')
            ->select('product_name, SUM(quantity) as total_sold')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('o.status', OrderModel::STATUS_COMPLETED)
            ->where('o.created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->groupBy('product_name')
            ->orderBy('total_sold', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        // Recent Activity Feed (Orders + New Users + Stock)
        $activities = [];

        try {
            // 1. Get recent order status changes
            $recentOrders = $db->table('order_status_history h')
                ->select('h.*, o.transaction_code')
                ->join('orders o', 'o.id = h.order_id')
                ->orderBy('h.created_at', 'DESC')
                ->limit(3)
                ->get()
                ->getResultArray();
            
            foreach ($recentOrders as $ro) {
                $activities[] = [
                    'title' => 'Order Updated',
                    'desc'  => "TXN: {$ro['transaction_code']} changed to {$ro['status_to']} by {$ro['changed_by']}",
                    'time'  => $ro['created_at'],
                    'icon'  => 'fa-shopping-bag',
                    'color' => '#818cf8'
                ];
            }

            // 2. Get recent stock changes (from products updated_at)
            $recentStock = $db->table('products')
                ->select('name, current_stock, updated_at')
                ->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->orderBy('updated_at', 'DESC')
                ->limit(3)
                ->get()
                ->getResultArray();

            foreach ($recentStock as $rs) {
                $activities[] = [
                    'title' => 'Stock Update',
                    'desc'  => "Product '{$rs['name']}' now has {$rs['current_stock']} units.",
                    'time'  => $rs['updated_at'],
                    'icon'  => 'fa-boxes',
                    'color' => '#10b981'
                ];
            }

            // 3. Get recent user changes
            $recentUsers = $db->table('users')
                ->select('username, role, email')
                ->orderBy('id', 'DESC')
                ->limit(2)
                ->get()
                ->getResultArray();

            foreach ($recentUsers as $ru) {
                $activities[] = [
                    'title' => 'New User Access',
                    'desc'  => "{$ru['username']} ({$ru['role']}) added to the system.",
                    'time'  => date('Y-m-d H:i:s'), // Assuming just now if no created_at
                    'icon'  => 'fa-user-plus',
                    'color' => '#fbbf24'
                ];
            }

            // Sort all activities by time
            usort($activities, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });

            $data['activities'] = array_slice($activities, 0, 8);
        } catch (\Exception $e) {
            log_message('error', 'Dashboard Activity Feed Error: ' . $e->getMessage());
            $data['activities'] = [];
        }

        return view('admin/dashboard', $data);
    }

    public function getTodaySalesData()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        // Ensure DB connection uses the same timezone as PHP (Asia/Manila)
        $db = \Config\Database::connect();
        $db->query("SET time_zone = '+08:00'");

        $orderModel = new OrderModel();
        $todaySales = round((float) $orderModel->getTodayRevenue(), 2);
        $yesterdaySales = round($orderModel->getDailyRevenue(date('Y-m-d', strtotime('-1 day'))), 2);
        
        $growth = 0;
        if ($yesterdaySales > 0) {
            $growth = (($todaySales - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($todaySales > 0) {
            $growth = 100;
        }
        $salesGrowth = round($growth, 1);

        return $this->response->setJSON([
            'today_sales' => $todaySales,
            'sales_growth' => $salesGrowth,
            'server_time' => date('M d, Y h:i A')
        ]);
    }
}