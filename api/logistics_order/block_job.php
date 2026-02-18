<?php
header("Content-Type: application/json");

include "../../config/database_conn.php";
include "../../models/logistics_orders_model.php";

$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$reason = $_POST['reason'] ?? '';

if (!$job_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing job_id"
    ]);
    exit;
}

$result = blockJobOrder($databaseconn, $job_id, $reason);

echo json_encode($result);
