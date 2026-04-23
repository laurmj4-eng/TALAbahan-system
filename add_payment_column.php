<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'mj_chatbot';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'COD' AFTER notes";

if ($conn->query($sql) === TRUE) {
    echo "Column 'payment_method' added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
