<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header("Content-Type: application/json");

require "../../config/database_conn.php";

$logFile = $_SERVER['DOCUMENT_ROOT'] . "/debug_update_location.txt";
file_put_contents($logFile, "\n--- NEW REQUEST ---\n", FILE_APPEND);
file_put_contents($logFile, "RAW POST: " . print_r($_POST, true), FILE_APPEND);
file_put_contents($logFile, "RAW INPUT: " . file_get_contents("php://input") . "\n", FILE_APPEND);

$trip_id   = $_POST['trip_id'] ?? null;
$driver_id = $_POST['driver_id'] ?? null;
$lat       = $_POST['latitude'] ?? null;
$lng       = $_POST['longitude'] ?? null;

if (!$trip_id || !$driver_id || !$lat || !$lng) {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters",
        "debug" => $_POST
    ]);
    exit;
}

$result = insertTrackingLog(
    $databaseconn,
    (int)$trip_id,
    (int)$driver_id,
    (float)$lat,
    (float)$lng
);

echo json_encode($result);

function insertTrackingLog($databaseconn, $trip_id, $driver_id, $lat, $lng)
{
    $databaseconn->begin_transaction();

    try {
        $stmt1 = $databaseconn->prepare("
            INSERT INTO tbl_tracking_logs 
            (trip_id, driver_id, latitude, longitude, recorded_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt1->bind_param("iidd", $trip_id, $driver_id, $lat, $lng);
        $stmt1->execute();

        $stmt2 = $databaseconn->prepare("
            UPDATE tbl_trips
            SET current_latitude = ?,
                current_longitude = ?,
                last_location_update = NOW()
            WHERE trip_id = ?
        ");
        $stmt2->bind_param("ddi", $lat, $lng, $trip_id);
        $stmt2->execute();

        $databaseconn->commit();

        return [
            "success" => true,
            "message" => "Location updated"
        ];
    } catch (Exception $e) {
        $databaseconn->rollback();
        
            file_put_contents(
        __DIR__ . "/debug_update_location.txt",
        "SQL ERROR: " . $e->getMessage() . "\n",
        FILE_APPEND
    );


        return [
            "success" => false,
            "message" => $e->getMessage(),
            "error" => $e->getMessage()
        ];
    }
}
