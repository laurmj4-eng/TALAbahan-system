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
    $routes->get('inventory/movements', 'InventoryController::movements');

    // --- Categories ---
    $routes->get('categories', 'CategoriesController::index');
    $routes->post('categories/store', 'CategoriesController::store');

    // --- Purchases (Inbound Stock) ---
    $routes->get('purchases', 'PurchasesController::index');
    $routes->post('purchases/receive', 'PurchasesController::receive');

    // --- Payments ---
    $routes->get('payments', 'PaymentsController::index');
    $routes->post('payments/store', 'PaymentsController::store');
    $routes->get('payments/markPaid/(:num)', 'PaymentsController::markPaid/$1');

    // --- Deliveries ---
    $routes->get('deliveries', 'DeliveriesController::index');
    $routes->post('deliveries/store', 'DeliveriesController::store');
    $routes->get('deliveries/markDelivered/(:num)', 'DeliveriesController::markDelivered/$1');

    // --- Order Routes ---
    $routes->get('orders', 'Orders::index');
    $routes->get('orders/show/(:num)', 'Orders::show/$1');
    $routes->get('orders/items', 'Orders::itemsPage');
    $routes->get('orders/items/(:num)', 'Orders::items/$1');
    $routes->post('orders/updateStatus', 'Orders::updateStatus');
});

// --- 3. STAFF GROUP ---
$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'staffGuard'], function($routes) {
    $routes->get('dashboard', 'StaffController::index');
});

// --- 4. CUSTOMER GROUP ---
$routes->group('customer', ['namespace' => 'App\Controllers\Customer', 'filter' => 'customerGuard'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
});