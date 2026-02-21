<?php
header('Content-Type: application/json');
include "../config/database_conn.php";

$sql = "
SELECT 
    t.trip_id,
    t.truck_plate_number AS plate_number,

    w.latitude AS origin_lat,
    w.longitude AS origin_lng,
    w.warehouse_name AS origin_name,

    j.id AS job_id,
    j.destination_lat,
    j.destination_lng,
    j.destination AS destination_name,
    j.status AS job_status,
    j.delivery_sequence,

    tl.latitude AS current_lat,
    tl.longitude AS current_lng

FROM tbl_trips t

LEFT JOIN tbl_warehouses w 
    ON t.warehouse_id = w.warehouse_id

LEFT JOIN tbl_job_orders j 
    ON j.trip_id = t.trip_id

LEFT JOIN (
    SELECT trip_id, latitude, longitude
    FROM tbl_tracking_logs
    WHERE tracking_log_id IN (
        SELECT MAX(tracking_log_id)
        FROM tbl_tracking_logs
        GROUP BY trip_id
    )
) tl ON tl.trip_id = t.trip_id

WHERE t.status = 'in_transit'

ORDER BY t.trip_id, j.delivery_sequence ASC
";

$result = $databaseconn->query($sql);

$trips = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {

        $trip_id = $row['trip_id'];

        // Create trip entry if not exists
        if (!isset($trips[$trip_id])) {
            $trips[$trip_id] = [
                "trip_id" => $trip_id,
                "plate_number" => $row['plate_number'],
                "origin_lat" => $row['origin_lat'],
                "origin_lng" => $row['origin_lng'],
                "origin_name" => $row['origin_name'],
                "current_lat" => $row['current_lat'],
                "current_lng" => $row['current_lng'],
                "jobs" => []
            ];
        }

        // Add job to trip
        if (!empty($row['job_id'])) {
            $trips[$trip_id]["jobs"][] = [
                "job_id" => $row['job_id'],
                "destination_lat" => $row['destination_lat'],
                "destination_lng" => $row['destination_lng'],
                "destination_name" => $row['destination_name'],
                "status" => $row['job_status'],
                "delivery_sequence" => $row['delivery_sequence']
            ];
        }
    }
}

// Convert associative array to indexed array
echo json_encode(array_values($trips));
