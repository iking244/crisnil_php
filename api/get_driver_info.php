<?php
header("Content-Type: application/json");
require_once "../config/database_conn.php"; // update if different

if (!isset($_GET['rider_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing rider_id"
    ]);
    exit;
}

$rider_id = intval($_GET['rider_id']);

$query = "
    SELECT 
        CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS driver_name,
        u.USER_STATUS AS work_status,
        t.truck_plate_number AS plate_number
    FROM crisnil_users u
    INNER JOIN tbl_trips t
        ON u.USER_ID = t.driver_id
    WHERE u.USER_ID = ?
    LIMIT 1
";

$stmt = $databaseconn->prepare($query);
$stmt->bind_param("i", $rider_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Driver not found"
    ]);
    exit;
}

$row = $result->fetch_assoc();

echo json_encode([
    "status" => "success",
    "driver_name" => $row['driver_name'],
    "work_status" => $row['work_status'],
    "plate_number" => $row['plate_number']
]);
