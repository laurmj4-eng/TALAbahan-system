<?php
require 'vendor/autoload.php';

$config = new Config\Database();
$db = $config->initialize();

$result = $db->query('SELECT id, transaction_code, customer_name, status, payment_status FROM orders LIMIT 10');

echo "=== Orders in Database ===\n";
foreach ($result->getResult() as $row) {
    echo "ID: {$row->id}, Code: {$row->transaction_code}, Customer: {$row->customer_name}, Status: {$row->status}, Payment: {$row->payment_status}\n";
}

echo "\n=== Pending/Unpaid Orders ===\n";
$pending = $db->query("SELECT id, transaction_code, status FROM orders WHERE status IN ('Pending', 'Unpaid') LIMIT 5");
foreach ($pending->getResult() as $row) {
    echo "ID: {$row->id}, Code: {$row->transaction_code}, Status: {$row->status}\n";
}
?>
