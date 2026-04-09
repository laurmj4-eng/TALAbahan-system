<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Load the URL helper so base_url() works
        helper('url');
        
        // This loads app/Views/dashboard.php
        return view('dashboard');
    }
}