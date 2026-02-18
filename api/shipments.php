<?php
require 'config.php';
require 'auth.php';

$riderId = requireAuth($conn);

$sql = "
SELECT
    t.tracking_number,
    t.plate_number,
    t.track_status,
    t.driver,

    o.warehouse_id AS origin_id,
    o.warehouse_name AS origin_name,
    o.latitude AS origin_lat,
    o.longitude AS origin_lng,

    d.warehouse_id AS destination_id,
    d.warehouse_name AS destination_name,
    d.latitude AS destination_lat,
    d.longitude AS destination_lng

FROM tbl_tracking t
JOIN tbl_warehouses o ON t.origin_id = o.warehouse_id
JOIN tbl_warehouses d ON t.destination_id = d.warehouse_id
WHERE t.driver_id = ?
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $riderId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$shipments = [];

while ($row = mysqli_fetch_assoc($result)) {
    $shipments[] = [
        "tracking_number" => $row['tracking_number'],
        "driver_name" => $row['driver'],
        "plate_number" => $row['plate_number'],
        "status" => $row['track_status'],
        "origin" => [
            "id" => $row['origin_id'],
            "name" => $row['origin_name'],
            "lat" => (float)$row['origin_lat'],
            "lng" => (float)$row['origin_lng']
        ],
        "destination" => [
            "id" => $row['destination_id'],
            "name" => $row['destination_name'],
            "lat" => (float)$row['destination_lat'],
            "lng" => (float)$row['destination_lng']
        ]
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $shipments
]);
