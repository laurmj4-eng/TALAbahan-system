<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\ProductModel; // IMPORTANT: Add this to fetch products!

class Dashboard extends BaseController
{
    public function index()
    {
        // 1. Security check
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        // 2. Fetch products from the database
        $productModel = new ProductModel();

        // 3. Prepare data for the view
        $data =[
            'title'    => 'Customer Portal',
            'username' => session()->get('username'),
            'products' => $productModel->findAll() // Sends products to the view
        ];

        // 4. Load the customer dashboard view
        return view('customer/dashboard', $data);
    }
}