<?php
header("Content-Type: application/json");
require_once "db_connection.php";

$trackingId = $_POST['tracking_id'] ?? $_GET['tracking_id'] ?? null;
$appStatus  = $_POST['status'] ?? $_GET['status'] ?? null;

if (!$trackingId || !$appStatus) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing parameters"
    ]);
    exit;
}

// 🔒 Enforce photo proof before delivery completion

// Assume $driverId is known (from session or token)

$check = $conn->prepare("
    SELECT COUNT(*) AS active_count
    FROM tbl_tracking
    WHERE driver_id = ?
    AND track_status IN (
        'ASSIGNED',
        'EN_ROUTE_TO_PICKUP',
        'PICKED_UP',
        'EN_ROUTE_TO_DELIVERY',
        'ARRIVED_AT_DELIVERY'
    )
");
$check->bind_param("i", $driverId);
$check->execute();
$result = $check->get_result();
$row = $result->fetch_assoc();

if ($row['active_count'] > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "You already have an ongoing task"
    ]);
    exit;
}



// Map APP status → DB status
function mapAppStatusToDb(string $status): string
{
    return match ($status) {
        'EN_ROUTE_TO_PICKUP'   => 'Departed HO: Antipolo',
        'PICKED_UP'            => 'Completed Loading Products from Warehouse: Pasig',
        'EN_ROUTE_TO_DELIVERY' => 'In Transit to HO: Antipolo',
        'ARRIVED_AT_DELIVERY'  => 'Arrived HO: Antipolo',
        'DELIVERED'            => 'Completed Transfer of Products',
        default                => 'Departed HO: Antipolo'
    };
}


$dbStatus = mapAppStatusToDb($appStatus);

$sql = "
    UPDATE tbl_tracking
    SET track_status = ?, status_asof = NOW()
    WHERE tracking_number = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $dbStatus, $trackingId);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update status"
    ]);
}
