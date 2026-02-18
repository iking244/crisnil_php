<?php
header("Content-Type: application/json");
require_once "db_connection.php";

//STATUS MAPPING FOR ANDROID
function mapTrackStatusToAppStatus(string $status): string
{
    if ($status === 'Cancelled Schedule') {
        return 'CANCELLED';
    }

    if ($status === 'Completed Transfer of Products') {
        return 'DELIVERED';
    }

    if (str_contains($status, 'Arrived HO')) {
        return 'ARRIVED_AT_DELIVERY';
    }

    if (str_contains($status, 'Arrived Warehouse')) {
        return 'ARRIVED_AT_PICKUP';
    }

    if (str_contains($status, 'Completed Loading')) {
        return 'PICKED_UP';
    }

    if (str_starts_with($status, 'Departed')) {
        return 'EN_ROUTE_TO_PICKUP';
    }

    return 'ASSIGNED';
}


$driverId = $_GET['driver_id'] ?? null;

if (!$driverId) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing driver_id"
    ]);
    exit;
}

$sql = "
    SELECT
        t.tracking_number AS id,
        t.track_status AS raw_status,

        o.warehouse_name AS pickup_address,
        o.latitude AS pickup_lat,
        o.longitude AS pickup_lng,

        d.warehouse_name AS delivery_address,
        d.latitude AS delivery_lat,
        d.longitude AS delivery_lng

    FROM tbl_tracking t
    INNER JOIN tbl_warehouses o ON t.origin_id = o.warehouse_id
    INNER JOIN tbl_warehouses d ON t.destination_id = d.warehouse_id

    WHERE t.driver_id = ?
    ORDER BY t.status_asof DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driverId);
$stmt->execute();
$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row["id"],
        "pickupAddress" => $row["pickup_address"],
        "pickupLat" => (float) $row["pickup_lat"],
        "pickupLng" => (float) $row["pickup_lng"],
        "deliveryAddress" => $row["delivery_address"],
        "deliveryLat" => (float) $row["delivery_lat"],
        "deliveryLng" => (float) $row["delivery_lng"],
        "raw_status" => $row["raw_status"],
        "status" => mapTrackStatusToAppStatus($row["raw_status"])
    ];
}


echo json_encode([
    "status" => "success",
    "data" => $data
]);
