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

// Added CSRF protection to all POST routes
$routes->post('auth/verify', 'Auth::verify', ['filter' => 'csrf']);
$routes->post('auth/create_account', 'Auth::createAccount', ['filter' => 'csrf']);

$routes->get('logout', 'Auth::logout');

// --- 2. ADMIN GROUP ---
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminGuard'], function($routes) {
    
    // --- Dashboard Overview ---
    $routes->get('dashboard', 'Dashboard::index');

    // --- User Management ---
    $routes->get('users', 'AdminController::users'); 
    $routes->post('saveUser', 'AdminController::saveUser', ['filter' => 'csrf']);
    $routes->post('updateUser', 'AdminController::updateUser', ['filter' => 'csrf']); 
    $routes->get('deleteUser/(:num)', 'AdminController::deleteUser/$1');
    
    // --- POS & Sales ---
    $routes->get('getProducts', 'PosController::getProducts');
    $routes->post('checkout', 'PosController::checkout', ['filter' => 'csrf']);
    $routes->get('getHistory', 'PosController::getHistory');
    
    // --- Daily Inventory (Products) ---
    $routes->get('products', 'ProductController::index');
    $routes->post('products/store', 'ProductController::store', ['filter' => 'csrf']);
    $routes->get('products/getDetails/(:num)', 'ProductController::getDetails/$1');
    $routes->post('products/update', 'ProductController::update', ['filter' => 'csrf']);
    $routes->post('products/delete', 'ProductController::delete', ['filter' => 'csrf']);

    // --- Order Routes ---
    $routes->get('orders', 'Orders::index');
    $routes->get('orders/show/(:num)', 'Orders::show/$1');
    $routes->get('orders/items', 'Orders::itemsPage');
    $routes->get('orders/items/(:num)', 'Orders::items/$1');
    $routes->post('orders/updateStatus', 'Orders::updateStatus', ['filter' => 'csrf']);
    $routes->post('orders/updateTracking', 'Orders::updateTracking', ['filter' => 'csrf']);
    $routes->get('orders/refunds', 'Orders::refunds');
    $routes->post('orders/refunds/update', 'Orders::updateRefundStatus', ['filter' => 'csrf']);

    // --- Shipping Management ---
    $routes->get('shipping', 'ShippingController::index');
    $routes->post('shipping/store', 'ShippingController::store', ['filter' => 'csrf']);
    $routes->post('shipping/update', 'ShippingController::update', ['filter' => 'csrf']);
    $routes->post('shipping/delete', 'ShippingController::delete', ['filter' => 'csrf']);
    $routes->get('shipping/getDetails/(:num)', 'ShippingController::getDetails/$1');

    // --- Voucher Management ---
    $routes->get('vouchers', 'VoucherController::index');
    $routes->post('vouchers/store', 'VoucherController::store', ['filter' => 'csrf']);
    $routes->post('vouchers/toggle', 'VoucherController::toggle', ['filter' => 'csrf']);
});

// --- 3. STAFF GROUP ---
$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'staffGuard'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'StaffController::index');
    
    // Product Management
    $routes->get('products', 'StaffController::products');
    $routes->get('getProducts', 'StaffController::getProducts');
    $routes->get('getLowStockProducts', 'StaffController::getLowStockProducts');
    $routes->get('getBestSellers', 'StaffController::getBestSellers');
    $routes->get('getInventorySummary', 'StaffController::getInventorySummary');
    
    // Order Management
    $routes->get('orders', 'StaffController::orders');
    $routes->get('getOrders', 'StaffController::getOrders');
    $routes->get('getOrderDetail/(:num)', 'StaffController::getOrderDetail/$1');
    $routes->post('updateOrderStatus', 'StaffController::updateOrderStatus', ['filter' => 'csrf']);
    
    // Sales History
    $routes->get('salesHistory', 'StaffController::salesHistory');
    $routes->get('getSalesHistory', 'StaffController::getSalesHistory');
});

// --- 4. CUSTOMER GROUP ---
$routes->group('customer', ['namespace' => 'App\Controllers\Customer', 'filter' => 'customerGuard'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('profile', 'Dashboard::profile');
    $routes->get('order-center', 'Dashboard::orderCenter');
    $routes->get('order-items', 'Dashboard::orderItems');
    $routes->post('precheckout', 'Dashboard::preCheckout', ['filter' => 'csrf']);
    $routes->post('placeOrder', 'Dashboard::placeOrder', ['filter' => 'csrf']);
    $routes->post('validate-location', 'Dashboard::validateLocation', ['filter' => 'csrf']);
    $routes->get('order-details/(:num)', 'Dashboard::orderDetails/$1');
    $routes->post('cancel-order', 'Dashboard::cancelOrder', ['filter' => 'csrf']);
    $routes->post('pay-now', 'Dashboard::payNow', ['filter' => 'csrf']);
    $routes->get('tracking/(:num)', 'Dashboard::tracking/$1');
    $routes->post('review', 'Dashboard::submitReview', ['filter' => 'csrf']);
    $routes->post('refund-request', 'Dashboard::submitRefundRequest', ['filter' => 'csrf']);
});