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
                'low_stock_count' => 0,
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
        $data['cards']['today_orders'] = (int) $orderModel
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        $productModel = new ProductModel();
        $data['cards']['low_stock_count'] = (int) $productModel
            ->where('current_stock <=', 5)
            ->countAllResults();

        return view('admin/dashboard', $data);
    }
}