<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            if ($role === 'admin') return redirect()->to('/admin/dashboard');
            if ($role === 'staff') return redirect()->to('/staff/dashboard');
            if ($role === 'customer') return redirect()->to('/customer/dashboard');
        }
        return inertia('LoginPage'); 
    }

    public function login()
    {
        return inertia('LoginPage');
    }

    public function register()
    {
        return inertia('RegisterPage');
    }
}
