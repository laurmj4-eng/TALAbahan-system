<?php

namespace App\Controllers;

class CustomerController extends BaseController
{
    public function index()
    {
        // 1. Security check: Kick out anyone who isn't a customer
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Prepare data for the view
        $data =[
            'title'    => 'Customer Portal',
            'username' => session()->get('username'),
        ];

        // 3. Load the customer dashboard view (Make sure you have a file at app/Views/customer/dashboard.php)
        return view('customer/dashboard', $data);
    }
}