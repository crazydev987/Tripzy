<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request for CORS
    http_response_code(200);
    exit();
}

require 'database.php'; // your PDO connection

try {
    $postdata = file_get_contents("php://input");
    if (!$postdata) {
        throw new Exception("No input data received");
    }

    $request = json_decode($postdata, true); // decode as associative array
    if (!$request) {
        throw new Exception("Invalid JSON");
    }

    $name = trim($request['name'] ?? '');
    $email = trim($request['email'] ?? '');
    $password = trim($request['password'] ?? '');

    if (!$name || !$email || !$password) {
        throw new Exception("All fields are required");
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => false, "message" => "Email already registered"]);
        exit();
    }

    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password_hash]);

    echo json_encode(["success" => true, "message" => "User registered successfully"]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
