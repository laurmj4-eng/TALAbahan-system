<?php

/**
 * InfinityFree Debugger
 * This file helps identify if the issue is with PHP, paths, or the framework.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>InfinityFree Debugger</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

$files_to_check = [
    'app/Config/Paths.php',
    'system/Boot.php',
    '.env',
    'public/index.php'
];

echo "<h2>File Check:</h2><ul>";
foreach ($files_to_check as $file) {
    $exists = file_exists(__DIR__ . '/' . $file) ? "✅ EXISTS" : "❌ MISSING";
    echo "<li>$file: $exists (" . __DIR__ . '/' . $file . ")</li>";
}
echo "</ul>";

echo "<h2>Directory Listing:</h2><pre>";
print_r(scandir(__DIR__));
echo "</pre>";

if (file_exists(__DIR__ . '/app/Config/Paths.php')) {
    echo "<h2>Attempting to load Paths.php...</h2>";
    try {
        require __DIR__ . '/app/Config/Paths.php';
        $paths = new Config\Paths();
        echo "✅ Paths class loaded successfully!<br>";
        echo "System Directory: " . $paths->systemDirectory . "<br>";
        
        if (file_exists($paths->systemDirectory . '/Boot.php')) {
            echo "✅ Boot.php found at " . $paths->systemDirectory . "/Boot.php<br>";
        } else {
            echo "❌ Boot.php NOT found at " . $paths->systemDirectory . "/Boot.php<br>";
        }
    } catch (Throwable $e) {
        echo "❌ Error loading Paths: " . $e->getMessage() . "<br>";
    }
}
