<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');
$routes->get('auth', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('auth/verify', 'Auth::verify');
$routes->get('logout', 'Auth::logout');



// dashboard routes
$routes->get('/dashboard', 'Dashboard::index');

// staff routes
$routes->get('staff/dashboard', 'StaffController::index');

// admin routes
$routes->get('admin/dashboard', 'AdminController::index');


// --- NEW CRUD ROUTES FOR USER MANAGEMENT ---
$routes->post('admin/saveUser', 'AdminController::saveUser');
$routes->get('admin/deleteUser/(:num)', 'AdminController::deleteUser/$1');
$routes->post('admin/updateUser', 'AdminController::updateUser');