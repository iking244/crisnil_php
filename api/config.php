<?php
$host = "localhost";
$user = "crisnil_db";
$pass = "crisnil123";
$db   = "crisnil_db";


$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

mysqli_set_charset($conn, "utf8");
