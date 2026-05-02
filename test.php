<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../app/Config/Paths.php';
$paths = new Config\Paths();
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require $paths->systemDirectory . '/Boot.php';

$app = CodeIgniter\Boot::bootWeb($paths);

echo "<h1>Debug Test - LIVE SERVER</h1>";

echo "<h2>1. Environment Variables:</h2>";
echo "<ul>";
echo "<li>app.baseURL: " . (env('app.baseURL') ? '<span style="color:green;">' . env('app.baseURL') . '</span>' : '<span style="color:red;">MISSING</span>') . "</li>";
echo "<li>CI_ENVIRONMENT: " . (env('CI_ENVIRONMENT') ? '<span style="color:green;">' . env('CI_ENVIRONMENT') . '</span>' : '<span style="color:red;">MISSING</span>') . "</li>";
echo "<li>DB Host: " . (env('database.default.hostname') ? '<span style="color:green;">' . env('database.default.hostname') . '</span>' : '<span style="color:red;">MISSING</span>') . "</li>";
echo "<li>DB Name: " . (env('database.default.database') ? '<span style="color:green;">' . env('database.default.database') . '</span>' : '<span style="color:red;">MISSING</span>') . "</li>";
echo "<li>DB User: " . (env('database.default.username') ? '<span style="color:green;">' . env('database.default.username') . '</span>' : '<span style="color:red;">MISSING</span>') . "</li>";
echo "<li>DB Password: " . (env('database.default.password') ? '<span style="color:green;">***SET***</span>' : '<span style="color:red;">***EMPTY***</span>') . "</li>";
echo "</ul>";

echo "<h2>2. Directories Check:</h2>";
$dirsToCheck = [
    '../writable',
    '../writable/cache',
    '../writable/logs',
    '../writable/session',
    '../writable/uploads',
    'uploads'
];
foreach ($dirsToCheck as $dir) {
    $exists = is_dir($dir);
    $writable = is_writable($dir);
    $color = ($exists && $writable) ? 'green' : 'red';
    $status = ($exists && $writable) ? '✅ OK' : '❌ ' . (!$exists ? 'Not Found' : 'Not Writable');
    echo "<p style='color: $color; font-weight: bold;'>$dir: $status</p>";
}

echo "<h2>3. Database Connection Test:</h2>";
try {
    $db = \Config\Database::connect();
    if ($db->connect()) {
        echo "<p style='color: green; font-weight: bold;'>✅ Database Connection SUCCESSFUL!</p>";
        
        echo "<h3>Testing Tables:</h3>";
        $tables = ['users', 'products', 'orders', 'order_items'];
        foreach ($tables as $table) {
            try {
                $count = $db->table($table)->countAll();
                echo "<p style='color: green;'>✅ Table '$table' exists, has $count records</p>";
            } catch (\Exception $e) {
                echo "<p style='color: red;'>❌ Table '$table' ERROR: " . $e->getMessage() . "</p>";
            }
        }
    }
} catch (\Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Database Connection FAILED!</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Session Write Test:</h2>";
try {
    session()->set('test', 'success');
    echo "<p style='color: green; font-weight: bold;'>✅ Session Write SUCCESSFUL!</p>";
} catch (\Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Session Write FAILED!</p>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>5. Check for PHP Errors (Log File):</h2>";
$logDir = '../writable/logs/';
if (is_dir($logDir)) {
    $logFiles = glob($logDir . 'log-*.php');
    if (!empty($logFiles)) {
        $latestLog = end($logFiles);
        echo "<p style='color: green;'>Latest log file: " . basename($latestLog) . "</p>";
        echo "<p>Last 50 lines of log:</p>";
        $logContent = file_get_contents($latestLog);
        $lines = explode("\n", $logContent);
        $lastLines = array_slice($lines, -50);
        echo "<pre style='background: #111; color: #eee; padding: 15px; max-height: 300px; overflow-y: scroll;'>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
    } else {
        echo "<p style='color: orange;'>No log files found yet.</p>";
    }
} else {
    echo "<p style='color: red;'>Logs directory not found: $logDir</p>";
}
?>