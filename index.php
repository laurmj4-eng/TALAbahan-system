<?php

// Check PHP version.
if (version_compare(PHP_VERSION, '8.1', '<')) {
    printf('Your PHP version is %s, but CodeIgniter 4 requires at least PHP 8.1.', PHP_VERSION);
    exit(1);
}

// FOR DEBUGGING ONLY - Remove these for production
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 */

// Universal path detection for app/Config/Paths.php
$pathsConfig = FCPATH . 'app/Config/Paths.php';

if (! file_exists($pathsConfig)) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Error: Cannot find app/Config/Paths.php.<br>';
    echo 'Current directory: ' . __DIR__ . '<br>';
    exit(1);
}

require $pathsConfig;
$paths = new Config\Paths();

// Fix FCPATH for InfinityFree root deployment to ensure assets load correctly
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'mjtalabahan.page.gd') !== false) {
    if (!defined('FCPATH')) {
        define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
    }
}

// Ensure .env is found in the same folder as Paths.php
$paths->envDirectory = dirname($pathsConfig, 3);

// For InfinityFree root deployment, force envDirectory to root if needed
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'mjtalabahan.page.gd') !== false) {
    $paths->envDirectory = __DIR__;
}

// Load the framework bootstrapper
if (!file_exists($paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php')) {
    echo 'Error: Cannot find ' . $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';
    exit(1);
}
require $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';

// Launch the application
exit(CodeIgniter\Boot::bootWeb($paths));
