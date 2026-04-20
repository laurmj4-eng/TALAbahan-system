<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- 1. SHARED ROUTES ---
$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('auth/verify', 'Auth::verify');
$routes->get('logout', 'Auth::logout');
$routes->get('register', 'Auth::register');
$routes->post('auth/create_account', 'Auth::createAccount');

// --- 2. ADMIN GROUP ---
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {
    // Dashboard & User Management
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('saveUser', 'AdminController::saveUser');
    $routes->post('updateUser', 'AdminController::updateUser');
    $routes->get('deleteUser/(:num)', 'AdminController::deleteUser/$1');
    
    // POS & Sales
    $routes->get('getProducts', 'PosController::getProducts');
    $routes->post('checkout', 'PosController::checkout');
    $routes->get('getHistory', 'PosController::getHistory');
    
    // Daily Inventory (Products)
    $routes->get('products', 'ProductController::index');
    // FIXED: Removed redundant 'admin/' and 'Admin\' because they are already in the group settings
    $routes->post('products/store', 'ProductController::store');
});

// --- 3. STAFF GROUP ---
$routes->group('staff', ['namespace' => 'App\Controllers\Staff'], function($routes) {
    $routes->get('dashboard', 'StaffController::index');
});

// --- 4. CUSTOMER GROUP ---
$routes->group('customer', ['namespace' => 'App\Controllers\Customer'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});