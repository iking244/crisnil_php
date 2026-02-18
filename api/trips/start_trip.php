<?php
header("Content-Type: application/json");

include "../../config/database_conn.php";
include "../../models/trips_model.php";

$trip_id = isset($_POST['trip_id']) ? (int)$_POST['trip_id'] : 0;

if (!$trip_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing trip_id"
    ]);
    exit;
}

$result = startTrip($databaseconn, $trip_id);

echo json_encode($result);
