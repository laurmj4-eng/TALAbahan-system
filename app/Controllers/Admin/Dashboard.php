<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel; // Add this!

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
            'users'    => $userModel->findAll() // This line fetches the data!
        ];

        return view('admin/dashboard', $data);
    }
}