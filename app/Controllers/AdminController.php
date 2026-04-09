<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    public function index()
    {
        // We match the StaffController logic EXACTLY
        // Change 'staff' to 'admin'
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access Denied: Admin Only');
        }

        $data = [
            'title' => 'Admin Dashboard',
            'username' => session()->get('username')
        ];

        return view('admin/dashboard', $data);
    }
}