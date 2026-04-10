<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default & Login Routes
$routes->get('/', 'Auth::index');
$routes->get('auth', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('auth/verify', 'Auth::verify');
$routes->get('logout', 'Auth::logout');

// Registration Routes (For Customers)
$routes->get('register', 'Auth::register');
$routes->post('auth/create_account', 'Auth::createAccount');

// Customer Dashboard Route
$routes->get('dashboard', 'Dashboard::index');

// Staff Routes
$routes->get('staff/dashboard', 'StaffController::index');

// Admin Routes
$routes->get('admin/dashboard', 'AdminController::index');

// --- NEW CRUD ROUTES FOR USER MANAGEMENT ---
$routes->post('admin/saveUser', 'AdminController::saveUser');
$routes->get('admin/deleteUser/(:num)', 'AdminController::deleteUser/$1');
$routes->post('admin/updateUser', 'AdminController::updateUser');

// Add these to your existing routes
$routes->get('api/pos/products', 'PosController::getProducts');
$routes->post('api/pos/checkout', 'PosController::checkout');


// POS and Sales Routes
$routes->get('api/pos/products', 'PosController::getProducts');
$routes->post('api/pos/checkout', 'PosController::checkout');
$routes->get('api/pos/history', 'PosController::getHistory'); // NEW ROUTE