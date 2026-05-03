<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\ProductModel;

class Dashboard extends BaseController
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
