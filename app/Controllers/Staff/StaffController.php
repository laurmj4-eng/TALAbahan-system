<?php

namespace App\Controllers\Staff; // Updated namespace for the Staff subfolder

// Import the BaseController from the parent directory
use App\Controllers\BaseController;

class StaffController extends BaseController
{
    public function index()
    {
        // Check if user is logged in and is a staff member
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login')->with('error', 'Access Denied: Staff Only');
        }

        $data = [
            'title'    => 'Staff Dashboard',
            'username' => session()->get('username')
        ];

        // Points to app/Views/staff/dashboard.php
        return view('staff/dashboard', $data);
    }
}