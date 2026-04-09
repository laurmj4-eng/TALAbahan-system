<?php

namespace App\Controllers;

class StaffController extends BaseController
{
    public function index()
    {
        // Check if user is logged in and is a staff member
        if (session()->get('role') !== 'staff') {
            return redirect()->to('/login')->with('error', 'Access Denied: Staff Only');
        }

        $data = [
            'title' => 'Staff Dashboard',
            'username' => session()->get('username')
        ];

        return view('staff/dashboard', $data);
    }
}