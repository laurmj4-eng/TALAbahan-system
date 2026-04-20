<?php

namespace App\Controllers\Customer; // Updated namespace for the subfolder

// Import the BaseController from the parent folder
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check: Kick out anyone who isn't a customer
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Prepare data for the view
        $data = [
            'title'    => 'Customer Portal',
            'username' => session()->get('username'),
        ];

        // 3. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }
}