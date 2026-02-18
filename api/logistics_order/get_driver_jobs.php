<?php
header("Content-Type: application/json");

include "../../config/database_conn.php";
include "../../models/logistics_orders_model.php";

$driver_id = isset($_GET['driver_id']) ? (int)$_GET['driver_id'] : 0;

if (!$driver_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing driver_id"
    ]);
    exit;
}

$result = getDriverJobOrders($databaseconn, $driver_id);

$jobs = [];

while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

echo json_encode([
    "success" => true,
    "jobs" => $jobs
]);
