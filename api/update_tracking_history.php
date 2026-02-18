<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['tracking_number'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$trackingNumber = $data['tracking_number'];


$insert = $conn->prepare("
    INSERT INTO tbl_tracking_details (
        tracking_number,
        plate_number,
        driver,
        helper,
        tracking_date,
        track_status,
        status_asof,
        origin_id,
        destination_id
    )
    SELECT
        tracking_number,
        plate_number,
        driver,
        helper,
        CURDATE(),
        track_status,
        NOW(),
        origin_id,
        destination_id
    FROM tbl_tracking
    WHERE tracking_number = ?
");

$insert->bind_param("s", $trackingNumber);
$insert->execute();

echo json_encode(["status" => "success"]);
