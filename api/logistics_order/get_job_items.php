<?php
header("Content-Type: application/json");

include "../../config/database_conn.php";
include "../../models/logistics_orders_model.php";

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

if (!$job_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing job_id"
    ]);
    exit;
}

$result = getJobItems($databaseconn, $job_id);

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    "success" => true,
    "items" => $items
]);
