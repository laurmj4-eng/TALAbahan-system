<?php
require __DIR__ . '/vendor/codeigniter4/framework/system/Database/MySQLi/Connection.php';
// Just read the env file
$env = parse_ini_file(__DIR__ . '/.env');
$host = $env['database.default.hostname'] ?? 'localhost';
$user = $env['database.default.username'] ?? 'root';
$pass = $env['database.default.password'] ?? '';
$dbname = $env['database.default.database'] ?? 'test';
$port = $env['database.default.port'] ?? 3306;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $pass);
    $stmt = $pdo->query("SHOW TABLES LIKE '%fcm%'");
    echo "FCM tables: " . json_encode($stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE '%notification%'");
    echo "Notification tables: " . json_encode($stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
    
    $stmt = $pdo->query("DESCRIBE users");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Users columns: " . json_encode($cols) . "\n";
    
    // Check for fcm_token column
    echo "Has fcm_token: " . (in_array('fcm_token', $cols) ? 'YES' : 'NO') . "\n";
    
    // Check migrations
    $stmt = $pdo->query("SELECT * FROM migrations WHERE version LIKE '2026-06-28%'");
    echo "FCM migrations: " . json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
