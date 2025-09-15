<?php
session_start();

// Database connection
$dsn = 'mysql:host=localhost;dbname=tripzy;charset=utf8mb4';
$username = 'root';  // your DB username
$password = '';      // your DB password

try {
    $db = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Return JSON error instead of redirect (better for API)
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}
