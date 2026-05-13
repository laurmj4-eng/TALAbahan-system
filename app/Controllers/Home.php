<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return inertia('LoginPage'); // Default to login or a LandingPage if you have one
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
