<?php

namespace App\Controllers\Admin; // Updated namespace for the subfolder

// Import the BaseController and the UserModel so this file can find them
use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\OrderModel; // Add OrderModel
use App\Models\ProductModel; // Add ProductModel

class AdminController extends BaseController
{
    /**
     * Display the Main Dashboard Overview
     */
    public function index()
    {
        // Simple security check: Ensure only admins can access
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $orderModel = new OrderModel();
        $productModel = new ProductModel();

        $data = [
            'title'    => 'Admin Dashboard',
            'username' => session()->get('username'),
            'cards'    => [
                'today_sales'      => 0,
                'today_orders'     => 0,
                'low_stock_count'  => 0,
            ],
            'chart'    => [
                'labels' => [],
                'sales'  => [],
            ],
        ];

        try {
            // Get today's sales and order count
            $today = date('Y-m-d');
            
            $todaySalesResult = $orderModel
                ->selectSum('total_amount')
                ->where('DATE(created_at)', $today)
                ->first();
            
            $data['cards']['today_sales'] = (float) ($todaySalesResult['total_amount'] ?? 0);

            $data['cards']['today_orders'] = (int) $orderModel
                ->where('DATE(created_at)', $today)
                ->countAllResults();

            // Get low stock count
            try {
                $data['cards']['low_stock_count'] = (int) $productModel
                    ->where('current_stock <=', 5)
                    ->countAllResults();
            } catch (\Exception $e) {
                log_message('error', 'Low stock count error: ' . $e->getMessage());
                $data['cards']['low_stock_count'] = 0;
            }

            // Get 7-day sales trend for chart
            $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
            try {
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
                            $dailySales = (float)($trend['daily_sales'] ?? 0);
                            break;
                        }
                    }
                    $chartSales[] = $dailySales;
                }

                $data['chart']['labels'] = $chartLabels;
                $data['chart']['sales'] = $chartSales;
            } catch (\Exception $e) {
                log_message('error', 'Sales trend error: ' . $e->getMessage());
                $data['chart']['labels'] = [];
                $data['chart']['sales'] = [];
            }
        } catch (\Exception $e) {
            log_message('error', 'Admin dashboard error: ' . $e->getMessage());
        }

        // Points to app/Views/admin/dashboard.php
        return view('admin/dashboard', $data);
    }

    /**
     * Display the Separate User Management (Database) Page
     */
    public function users()
    {
        // Security check
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied...');
        }

        $userModel = new UserModel();
        $data = [
            'title' => 'Database Management',
            'users' => $userModel->findAll() // Fetches all users for the table
        ];

        // Points to the new separate view: app/Views/admin/user_view.php
        return view('admin/user_view', $data);
    }

    /**
     * Save a new User (Append Entity)
     */
    public function saveUser()
    {
        $userModel = new UserModel();
        
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // Ideally use password_hash() in Model
            'role'     => $this->request->getPost('role'),
        ];
        
        if (! $userModel->insert($data)) {
            return redirect()->back()->with('error', implode(' ', $userModel->errors()))->withInput();
        }
        
        // REDIRECT FIX: Go back to the Users page, not the dashboard
        return redirect()->to('/admin/users')->with('msg', 'User successfully added to the database!');
    }

    /**
     * Update an existing User (Override Protocol)
     */
    public function updateUser()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'username' => $this->request->getPost('username'), 
            'email'    => $this->request->getPost('email'),
            'role'     => $this->request->getPost('role'),
        ];

        // Only update password if the admin typed a new one
        if(!empty($this->request->getPost('password'))) {
            $data['password'] = $this->request->getPost('password');
        }

        if (! $userModel->update($id, $data)) {
            return redirect()->back()->with('error', implode(' ', $userModel->errors()))->withInput();
        }

        // REDIRECT FIX: Stay on the Users page
        return redirect()->to('/admin/users')->with('msg', 'User protocol updated successfully!');
    }

    /**
     * Delete a User (Terminate Node)
     */
    public function deleteUser($id)
    {
        if ((int) session()->get('user_id') === (int) $id) {
            return redirect()->to('/admin/users')->with('error', 'You cannot delete your own active account.');
        }

        $userModel = new UserModel();
        $userModel->delete($id);
        
        // REDIRECT FIX: Stay on the Users page
        return redirect()->to('/admin/users')->with('msg', 'User terminated from the system.');
    }
}