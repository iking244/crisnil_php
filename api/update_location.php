<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
require "../config/database_conn.php";

// Read raw input (for JSON requests)
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Support both JSON and POST
$trip_id   = $data['trip_id']   ?? $_POST['trip_id']   ?? null;
$driver_id = $data['driver_id'] ?? $_POST['driver_id'] ?? null;
$lat       = $data['latitude']  ?? $_POST['latitude']  ?? null;
$lng       = $data['longitude'] ?? $_POST['longitude'] ?? null;

if (!$trip_id || !$driver_id || !$lat || !$lng) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters",
        "debug" => [
            "trip_id" => $trip_id,
            "driver_id" => $driver_id,
            "lat" => $lat,
            "lng" => $lng
        ]
    ]);
    exit;
}

$result = insertTrackingLog(
    $conn,   // fixed variable
    (int)$trip_id,
    (int)$driver_id,
    (float)$lat,
    (float)$lng
);

echo json_encode($result);

function insertTrackingLog($conn, $trip_id, $driver_id, $lat, $lng)
{
    $conn->begin_transaction();

    try {
        // 1. Insert into tracking logs
        $stmt1 = $conn->prepare("
            INSERT INTO tbl_tracking_logs 
            (trip_id, driver_id, latitude, longitude, recorded_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt1->bind_param("iidd", $trip_id, $driver_id, $lat, $lng);
        $stmt1->execute();

        // 2. Update current location in trips table
        $stmt2 = $conn->prepare("
            UPDATE tbl_trips
            SET current_latitude = ?,
                current_longitude = ?,
                last_location_update = NOW()
            WHERE trip_id = ?
        ");
        $stmt2->bind_param("ddi", $lat, $lng, $trip_id);
        $stmt2->execute();

        // 3. Cleanup old logs (keep last 300)
        $cleanup = $conn->prepare("
            DELETE FROM tbl_tracking_logs
            WHERE trip_id = ?
            AND tracking_log_id NOT IN (
                SELECT tracking_log_id FROM (
                    SELECT tracking_log_id
                    FROM tbl_tracking_logs
                    WHERE trip_id = ?
                    ORDER BY tracking_log_id DESC
                    LIMIT 300
                ) as temp
            )
        ");
        $cleanup->bind_param("ii", $trip_id, $trip_id);
        $cleanup->execute();

        $conn->commit();

        return [
            "success" => true,
            "message" => "Location updated"
        ];
    } catch (Exception $e) {
        $conn->rollback();

        return [
            "success" => false,
            "message" => "Tracking update failed",
            "error" => $e->getMessage()
        ];
    }
}
