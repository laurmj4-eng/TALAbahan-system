<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'mj_chatbot';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER unit";

if ($conn->query($sql) === TRUE) {
    echo "Column 'image' added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
