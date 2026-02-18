<?php
header("Content-Type: application/json");
include __DIR__ . "/../config/database_conn.php";

// Check connection
if (!isset($databaseconn)) {
    die(json_encode([
        "success" => false,
        "message" => "Database connection not found."
    ]));
}

$trip_id = $_POST['trip_id'] ?? null;
$status = $_POST['status'] ?? null;

$allowed_status = [
    "pending_loading",
    "loading",
    "ready_to_depart",
    "in_transit",
    "completed",
    "cancelled"
];

if (!$trip_id || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters"
    ]);
    exit;
}

if (!in_array($status, $allowed_status)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid status value"
    ]);
    exit;
}

$stmt = $databaseconn->prepare("
    UPDATE tbl_trips
    SET status = ?
    WHERE trip_id = ?
");

$stmt->bind_param("si", $status, $trip_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Status updated successfully"
    ]);
    header("Location: ../views/trip_details.php?trip_id=$trip_id&status=success");
} else {
    echo json_encode([
        "success" => false,
        "message" => "Update failed"
    ]);
    header("Location: ../views/trip_details.php?trip_id=$trip_id&status=false");
}

$stmt->close();
$databaseconn->close();
?>
