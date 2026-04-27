<?php

/**
 * TALAbahan System - Root Entry Point
 * Optimized for InfinityFree and Shared Hosting
 */

// 1. Check PHP version
if (version_compare(PHP_VERSION, '8.1', '<')) {
    printf('Your PHP version is %s, but CodeIgniter 4 requires at least PHP 8.1.', PHP_VERSION);
    exit(1);
}

// 2. Enable Error Reporting for Debugging (Temporary)
// On InfinityFree, 500 errors are often silent. This helps see what's wrong.
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 3. Define the Front Controller Path
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// 4. Load the Paths Config
// Since this file is in the root, Paths.php is in app/Config/
$pathsPath = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Paths.php';

if (!file_exists($pathsPath)) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'Error: Cannot find app/Config/Paths.php.<br>';
    echo 'Current directory: ' . __DIR__ . '<br>';
    exit(1);
}

require $pathsPath;
$paths = new Config\Paths();

// 5. Override paths if needed for root deployment
// This ensures CI knows where the app and system folders are
$paths->appDirectory    = __DIR__ . DIRECTORY_SEPARATOR . 'app';
$paths->systemDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'system';
$paths->writableDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'writable';
$paths->envDirectory    = __DIR__;

// 6. Load the framework bootstrapper
if (!file_exists($paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php')) {
    echo 'Error: Cannot find ' . $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';
    exit(1);
}

require $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';

// 7. Launch the application
exit(CodeIgniter\Boot::bootWeb($paths));
