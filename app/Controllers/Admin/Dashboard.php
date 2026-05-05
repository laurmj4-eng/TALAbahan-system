<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\OrderModel;
use App\Models\ProductModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        
        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'users'    => $userModel->findAll(),
            'cards'    => [
                'today_sales'     => 0,
                'today_orders'    => 0,
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
        
        // Calculate Growth Metric (Today vs Yesterday)
        $yesterdaySales = round($orderModel->getDailyRevenue(date('Y-m-d', strtotime('-1 day'))), 2);
        $growth = 0;
        if ($yesterdaySales > 0) {
            $growth = (($data['cards']['today_sales'] - $yesterdaySales) / $yesterdaySales) * 100;
        } elseif ($data['cards']['today_sales'] > 0) {
            $growth = 100; // 100% growth if yesterday was 0
        }
        $data['cards']['sales_growth'] = round($growth, 1);

        $data['cards']['today_orders'] = (int) $orderModel
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

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

        // Recent Activity Feed (Orders + New Users)
        $activities = [];

        try {
            // 1. Get recent order status changes
            $recentOrders = $db->table('order_status_history h')
                ->select('h.*, o.transaction_code')
                ->join('orders o', 'o.id = h.order_id')
                ->orderBy('h.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
            
            foreach ($recentOrders as $order) {
                $activities[] = [
                    'type' => 'order',
                    'title' => "Order #{$order['transaction_code']}",
                    'desc' => "Status changed to <strong>{$order['status_to']}</strong> by {$order['changed_by']}",
                    'time' => $order['created_at'],
                    'icon' => 'fa-box',
                    'color' => '#6366f1'
                ];
            }

            // 2. Get recent user registrations
            $recentUsers = $db->table('users')
                ->orderBy('created_at', 'DESC')
                ->limit(3)
                ->get()
                ->getResultArray();
            
            foreach ($recentUsers as $user) {
                $activities[] = [
                    'type' => 'user',
                    'title' => "New User: " . ($user['full_name'] ?: $user['username']),
                    'desc' => "Joined the platform as a " . ucfirst($user['role']),
                    'time' => $user['created_at'],
                    'icon' => 'fa-user-plus',
                    'color' => '#10b981'
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't crash the dashboard if history table is missing or has issues
            log_message('error', 'Dashboard Activity Feed Error: ' . $e->getMessage());
        }

        // Sort combined activities by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        $data['activities'] = array_slice($activities, 0, 6); // Top 6 most recent

        return view('admin/dashboard', $data);
    }
}