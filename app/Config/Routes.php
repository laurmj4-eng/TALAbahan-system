<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- 1. SHARED ROUTES ---
// Chatbot remains shared but is now handled by the Admin namespace with internal role checks
$routes->post('admin/chatbot/process', '\App\Controllers\Admin\Chatbot::process');
$routes->post('admin/chatbot/deleteHistory', '\App\Controllers\Admin\Chatbot::deleteHistory', ['filter' => 'csrf']);
// Removed 'throttle' from here to fix the error
$routes->get('/', 'Home::index');
$routes->get('login', 'Home::login');
$routes->get('logout', 'Auth::logout');
$routes->get('register', 'Home::register');

// --- API ROUTES (JSON) ---
$routes->options('api/(:any)', function() {
    return response()->setStatusCode(200);
});
$routes->group('api', function($routes) {
    $routes->post('auth/verify', 'Auth::verify');
    $routes->post('auth/register', 'Auth::createAccountApi');
    $routes->get('admin/products/list', '\App\Controllers\Admin\ProductController::list');
    $routes->post('admin/products/toggleStatus/(:num)', '\App\Controllers\Admin\ProductController::toggleStatus/$1');
    $routes->post('admin/products/delete', '\App\Controllers\Admin\ProductController::delete');
    $routes->get('staff/getOrders', '\App\Controllers\Staff\Orders::getOrders');
    $routes->post('staff/updateOrderStatus', '\App\Controllers\Staff\Orders::updateOrderStatus');
    $routes->get('customer/dashboard/data', '\App\Controllers\Customer\Dashboard::getData');
    
    // Admin API Routes
    $routes->get('admin/dashboard/data', '\App\Controllers\Admin\Dashboard::getData');
    $routes->get('admin/getProducts', '\App\Controllers\Admin\PosController::getProducts');
    $routes->get('admin/getHistory', '\App\Controllers\Admin\PosController::getHistory');
    $routes->get('admin/orders', '\App\Controllers\Admin\Orders::getOrders');
    $routes->post('admin/orders/updateStatus', '\App\Controllers\Admin\Orders::updateStatus');
    $routes->post('admin/orders/updateTracking', '\App\Controllers\Admin\Orders::updateTracking');
    $routes->get('admin/activity', '\App\Controllers\Admin\ActivityLogController::getLogs');
    $routes->get('admin/activity/user/(:num)', '\App\Controllers\Admin\ActivityLogController::userTimelineApi/$1');
    $routes->get('admin/shipping', '\App\Controllers\Admin\ShippingController::getLocations');
    $routes->get('admin/vouchers', '\App\Controllers\Admin\VoucherController::getVouchers');
    $routes->get('admin/users', '\App\Controllers\Admin\AdminController::getUsers');
    $routes->post('admin/users/save', '\App\Controllers\Admin\AdminController::saveUser');
    $routes->post('admin/users/update', '\App\Controllers\Admin\AdminController::updateUser');
    $routes->post('admin/users/delete/(:num)', '\App\Controllers\Admin\AdminController::deleteUser/$1');
});

// Keep existing routes for now but they should eventually be migrated to api group

// --- 2. ADMIN GROUP ---
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminGuard'], function($routes) {
    
    // --- Dashboard Overview ---
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/todaySales', 'Dashboard::getTodaySalesData');

    // --- Activity Monitoring ---
    $routes->get('activity', 'ActivityLogController::index');
    $routes->get('activity/user/(:num)', 'ActivityLogController::userTimeline/$1');

    // --- User Management ---
    $routes->get('users', 'AdminController::users'); 
    $routes->post('saveUser', 'AdminController::saveUser', ['filter' => 'csrf']);
    $routes->post('updateUser', 'AdminController::updateUser', ['filter' => 'csrf']); 
    $routes->get('deleteUser/(:num)', 'AdminController::deleteUser/$1');
    
    // --- POS & Sales ---
    $routes->get('pos', 'PosController::index');
    $routes->get('sales', 'SalesController::index');
    $routes->get('getProducts', 'PosController::getProducts');
    $routes->post('checkout', 'PosController::checkout', ['filter' => 'csrf']);
    $routes->get('getHistory', 'PosController::getHistory');
    
    // --- Daily Inventory (Products) ---
    $routes->get('products', 'ProductController::index');
    $routes->get('products/list', 'ProductController::list');
    $routes->post('products/store', 'ProductController::store', ['filter' => 'csrf']);
    $routes->get('products/getDetails/(:num)', 'ProductController::getDetails/$1');
    $routes->post('products/update', 'ProductController::update', ['filter' => 'csrf']);
    $routes->post('products/delete', 'ProductController::delete', ['filter' => 'csrf']);
    $routes->post('products/toggleStatus/(:num)', 'ProductController::toggleStatus/$1', ['filter' => 'csrf']);

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
    $routes->post('shipping/updateGlobal', 'ShippingController::updateGlobalShipping', ['filter' => 'csrf']);

    // --- Voucher Management ---
    $routes->get('vouchers', 'VoucherController::index');
    $routes->post('vouchers/store', 'VoucherController::store', ['filter' => 'csrf']);
    $routes->post('vouchers/toggle', 'VoucherController::toggle', ['filter' => 'csrf']);
});

// --- 3. STAFF GROUP ---
$routes->group('staff', ['namespace' => 'App\Controllers\Staff', 'filter' => 'staffGuard'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    
    // Product Management
    $routes->get('products', 'Products::index');
    $routes->get('getProducts', 'Products::getProducts');
    $routes->get('getLowStockProducts', 'Products::getLowStockProducts');
    $routes->get('getBestSellers', 'Products::getBestSellers');
    $routes->get('getInventorySummary', 'Dashboard::getInventorySummary');
    
    // Order Management
    $routes->get('orders', 'Orders::index');
    $routes->get('getOrders', 'Orders::getOrders');
    $routes->get('getOrderDetail/(:num)', 'Orders::getOrderDetail/$1');
    $routes->post('updateOrderStatus', 'Orders::updateOrderStatus', ['filter' => 'csrf']);
    
    // Sales History
    $routes->get('salesHistory', 'Sales::salesHistory');
    $routes->get('getSalesHistory', 'Sales::getSalesHistory');
});

// --- 4. CUSTOMER GROUP ---
$routes->group('customer', ['namespace' => 'App\Controllers\Customer', 'filter' => 'customerGuard'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('profile', 'Profile::index');
    $routes->get('order-center', 'Orders::orderCenter');
    $routes->get('order-items', 'Orders::orderItems');
    $routes->post('precheckout', 'Checkout::preCheckout', ['filter' => 'csrf']);
    $routes->post('placeOrder', 'Checkout::placeOrder', ['filter' => 'csrf']);
    $routes->post('validate-location', 'Checkout::validateLocation', ['filter' => 'csrf']);
    $routes->get('order-details/(:num)', 'Orders::orderDetails/$1');
    $routes->post('cancel-order', 'Orders::cancelOrder', ['filter' => 'csrf']);
    $routes->post('pay-now', 'Orders::payNow', ['filter' => 'csrf']);
    $routes->get('tracking/(:num)', 'Orders::tracking/$1');
    $routes->post('review', 'Reviews::submitReview', ['filter' => 'csrf']);
    $routes->post('refund-request', 'Refunds::submitRefundRequest', ['filter' => 'csrf']);
});

// --- 5. SPA CATCH-ALL ROUTES ---
// These must be at the very bottom to allow specific routes to match first
$routes->get('admin/(:any)', 'Home::index');
$routes->get('staff/(:any)', 'Home::index');
$routes->get('customer/(:any)', 'Home::index');