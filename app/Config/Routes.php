<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- 1. SHARED ROUTES ---
// 'guest' filter prevents logged-in users from seeing the login/register pages again
$routes->get('/', 'Auth::index', ['filter' => 'guest']);
$routes->get('login', 'Auth::index', ['filter' => 'guest']);
$routes->get('register', 'Auth::register', ['filter' => 'guest']);

// 'throttle' filter protects these routes from brute-force attacks
$routes->post('auth/verify', 'Auth::verify', ['filter' => 'throttle']);
$routes->post('auth/create_account', 'Auth::createAccount', ['filter' => 'throttle']);

$routes->get('logout', 'Auth::logout');

// --- 2. ADMIN GROUP ---
// Changed 'isAdmin' to 'adminGuard' to match your Filters.php alias
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminGuard'], function($routes) {
    
    // --- Dashboard Overview ---
    $routes->get('dashboard', 'Dashboard::index');

    // --- User Management ---
    $routes->get('users', 'AdminController::users'); 
    $routes->post('saveUser', 'AdminController::saveUser');
    $routes->post('updateUser', 'AdminController::updateUser'); 
    $routes->get('deleteUser/(:num)', 'AdminController::deleteUser/$1');
    
    // --- POS & Sales ---
    $routes->get('getProducts', 'PosController::getProducts');
    $routes->post('checkout', 'PosController::checkout');
    $routes->get('getHistory', 'PosController::getHistory');
    
    // --- Daily Inventory (Products) ---
    $routes->get('products', 'ProductController::index');
    $routes->post('products/store', 'ProductController::store');

    // --- Order Routes ---
    $routes->get('orders', 'Orders::index');
});

// --- 3. STAFF GROUP ---
// Changed 'isStaff' to 'staffGuard' to match your Filters.php alias
$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'staffGuard'], function($routes) {
    $routes->get('dashboard', 'StaffController::index');
});

// --- 4. CUSTOMER GROUP ---
// Changed 'isCustomer' to 'customerGuard' to match your Filters.php alias
$routes->group('customer', ['namespace' => 'App\Controllers\Customer', 'filter' => 'customerGuard'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});