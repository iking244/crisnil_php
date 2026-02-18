<?php
$host = "localhost";
$user = "root";
$pass = ""; // empty for XAMPP
$db   = "crisnil_db"; // <-- YOUR LOCAL DB NAME
//F!jO5MbN6
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}