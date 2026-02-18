<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require '../config/database_conn.php';

// Read raw input ONCE
$rawInput = file_get_contents("php://input");
file_put_contents("debug.txt", $rawInput);

// Decode JSON
$data = json_decode($rawInput, true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Username and password required"]);
    exit;
}

// Prepare select query
$sql = "SELECT USER_ID, USER_PASSWORD FROM crisnil_users WHERE USER_NAME = ?";
$stmt = mysqli_prepare($databaseconn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database prepare failed",
        "details" => mysqli_error($databaseconn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

// Password check
if (!$user || $password !== $user['USER_PASSWORD']) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(32));

// Prepare update query
$update = "UPDATE crisnil_users SET api_token = ? WHERE USER_ID = ?";
$stmt = mysqli_prepare($databaseconn, $update);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "error" => "Token update failed",
        "details" => mysqli_error($databaseconn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $token, $user['USER_ID']);
mysqli_stmt_execute($stmt);

// Success response
echo json_encode([
    "status" => "success",
    "rider_id" => $user['USER_ID'],
    "token" => $token
]);
