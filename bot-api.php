<?php
/**
 * MJ Chatbot PHP Bridge API
 * This file allows the Node.js chatbot to securely access sales data.
 * 
 * Instructions:
 * 1. Re-upload this file to your InfinityFree root directory.
 * 2. Ensure your .env has SECRET_TOKEN=mj_pogi_secret_2024_xtreme
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Content-Type: application/json');

// 1. Basic Path Setup
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$pathsConfig = FCPATH . 'app/Config/Paths.php';

if (!file_exists($pathsConfig)) {
    http_response_code(500);
    echo json_encode(['error' => 'Paths config not found.']);
    exit;
}

require $pathsConfig;
$paths = new Config\Paths();
$paths->envDirectory = __DIR__;

// 2. Load the framework bootstrapper (required for Database & Env)
require $paths->systemDirectory . DIRECTORY_SEPARATOR . 'Boot.php';

// Manually load .env to ensure SECRET_TOKEN is available
if (file_exists('.env')) {
    $envLines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value, '"\' ');
    }
}

// 3. Security Check
$secretToken = $_ENV['SECRET_TOKEN'] ?? 'mj_pogi_secret_2024_xtreme';
$providedToken = $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';

// Handle Bearer prefix if present
if (strpos($providedToken, 'Bearer ') === 0) {
    $providedToken = substr($providedToken, 7);
}

if ($providedToken !== $secretToken) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Unauthorized Access.',
        'debug_hint' => 'Token mismatch or missing Authorization header.'
    ]);
    exit;
}

// 4. Database Connection & Data Retrieval
try {
    $db = \Config\Database::connect();
    $action = $_GET['action'] ?? 'summary';

    if ($action === 'summary') {
        // Total Sales History
        $builder = $db->table('sales_history');
        $query = $builder->select('SUM(total_amount) as total_revenue, COUNT(id) as total_transactions')->get();
        $data = $query->getRowArray();
        
        // Today's Sales
        $todayQuery = $db->table('sales_history')
            ->select('SUM(total_amount) as today_revenue, COUNT(id) as today_transactions')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->get();
        $todayData = $todayQuery->getRowArray();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_revenue' => round((float)($data['total_revenue'] ?? 0), 2),
                'total_transactions' => (int)($data['total_transactions'] ?? 0),
                'today_revenue' => round((float)($todayData['today_revenue'] ?? 0), 2),
                'today_transactions' => (int)($todayData['today_transactions'] ?? 0),
                'currency' => 'PHP',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } 
    elseif ($action === 'latest') {
        $builder = $db->table('sales_history');
        $query = $builder->orderBy('created_at', 'DESC')->limit(5)->get();
        $transactions = $query->getResultArray();
        
        echo json_encode([
            'status' => 'success',
            'data' => $transactions
        ]);
    }
    else {
        echo json_encode([
            'status' => 'success', 
            'message' => 'MJ Chatbot Bridge is online.',
            'php_version' => PHP_VERSION
        ]);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.', 'message' => $e->getMessage()]);
}
