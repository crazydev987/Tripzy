<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'database.php'; // your PDO connection

try {
    $postdata = file_get_contents("php://input");
    if (!$postdata) throw new Exception("No input data received");

    $request = json_decode($postdata, true);
    if (!$request) throw new Exception("Invalid JSON");

    $email = trim($request['email'] ?? '');
    $password = trim($request['password'] ?? '');

    if (!$email || !$password) throw new Exception("Email and password are required");

    $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
        exit();
    }

    // Login success
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];

    echo json_encode(["success" => true, "message" => "Login successful"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
