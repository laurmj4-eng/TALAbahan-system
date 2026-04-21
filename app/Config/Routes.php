<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- 1. SHARED ROUTES ---
// Removed 'throttle' from here to fix the error
$routes->get('/', 'Auth::index', ['filter' => 'guest']);
$routes->get('login', 'Auth::index', ['filter' => 'guest']);
$routes->get('register', 'Auth::register', ['filter' => 'guest']);

// FIXED: Removed ['filter' => 'throttle'] from these two lines
$routes->post('auth/verify', 'Auth::verify');
$routes->post('auth/create_account', 'Auth::createAccount');

$routes->get('logout', 'Auth::logout');

// --- 2. ADMIN GROUP ---
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
    $routes->get('orders/show/(:num)', 'Orders::show/$1');
    $routes->get('orders/items', 'Orders::itemsPage');
    $routes->get('orders/items/(:num)', 'Orders::items/$1');
    $routes->post('orders/updateStatus', 'Orders::updateStatus');
});

// --- 3. STAFF GROUP ---
$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'staffGuard'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'StaffController::index');
    
    // Product Management
    $routes->get('products', 'StaffController::products');
    $routes->get('getProducts', 'StaffController::getProducts');
    $routes->post('addProduct', 'StaffController::addProduct');
    $routes->post('updateProduct', 'StaffController::updateProduct');
    $routes->post('updateStock', 'StaffController::updateStock');
    $routes->get('getLowStockProducts', 'StaffController::getLowStockProducts');
    $routes->get('getBestSellers', 'StaffController::getBestSellers');
    $routes->get('getInventorySummary', 'StaffController::getInventorySummary');
    
    // Order Management
    $routes->get('orders', 'StaffController::orders');
    $routes->get('getOrders', 'StaffController::getOrders');
    $routes->get('getOrderDetail/(:num)', 'StaffController::getOrderDetail/$1');
    
    // Sales History
    $routes->get('salesHistory', 'StaffController::salesHistory');
    $routes->get('getSalesHistory', 'StaffController::getSalesHistory');
});

// --- 4. CUSTOMER GROUP ---
$routes->group('customer', ['namespace' => 'App\Controllers\Customer', 'filter' => 'customerGuard'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});