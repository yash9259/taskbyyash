<?php
require_once __DIR__ . '/config.php';

$conn = getDbConnection();

$createTableSql = "
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
";

if (!$conn->query($createTableSql)) {
    die('Failed to create table: ' . $conn->error);
}

$conn->close();
