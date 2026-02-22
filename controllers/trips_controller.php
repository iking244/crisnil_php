<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../config/database_conn.php";
include "../models/trips_model.php";
include "../models/job_order_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   UTILITY
========================= */
function countActiveTrips($conn)
{
    $sql = "SELECT COUNT(*) as total 
            FROM tbl_trips 
            WHERE status IN 
            ('assigned', 'in_transit', 'loading', 'pending_loading', 'ready_to_depart')";

    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
    return 0;
}

/* =========================
   AJAX: GET UNSCHEDULED JOBS
========================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_jobs') {

    header('Content-Type: application/json');

    $warehouse_id = $_GET['warehouse_id'] ?? null;

    if (!$warehouse_id) {
        echo json_encode([]);
        exit();
    }

    $stmt = $databaseconn->prepare("
        SELECT j.id, j.destination
        FROM tbl_job_orders j
        INNER JOIN tbl_warehouses w
            ON j.origin = w.warehouse_name
        WHERE j.trip_id IS NULL
        AND j.status = 'pending'
        AND w.warehouse_id = ?
        ");
    $stmt->bind_param("i", $warehouse_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $jobs[] = [
            "id" => $row["id"],
            "label" => "#{$row['id']} - {$row['destination']}"
        ];
    }

    echo json_encode($jobs);
    exit();
}

/* =========================
   HANDLE POST ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? null;

    switch ($action) {

        /* =========================
           CREATE TRIP (Manual)
        ========================== */

        case 'create':

            $databaseconn->begin_transaction();

            try {

                $truck_id       = $_POST['truck_id'] ?? null;
                $driver_id      = $_POST['driver_id'] ?? null;
                $warehouse_id   = $_POST['warehouse_id'] ?? null;
                $departure_time = $_POST['departure_time'] ?? null;
                $job_ids        = $_POST['job_ids'] ?? [];

                if (!$truck_id || !$warehouse_id || !$driver_id) {
                    throw new Exception("Missing required fields.");
                }

                // Get truck plate number only (NOT driver anymore)
                $stmt = $databaseconn->prepare("
            SELECT plate_num
            FROM tbl_fleetlist
            WHERE PK_FLEET = ?
        ");
                $stmt->bind_param("i", $truck_id);
                $stmt->execute();
                $truck = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$truck) {
                    throw new Exception("Truck not found.");
                }

                $plate_num = $truck['plate_num'];

                // Optional: validate driver exists
                $stmt = $databaseconn->prepare("
            SELECT USER_ID
            FROM crisnil_users
            WHERE USER_ID = ?
            AND USER_BIO = 'RIDER'
        ");
                $stmt->bind_param("i", $driver_id);
                $stmt->execute();
                $driver = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$driver) {
                    throw new Exception("Invalid driver selected.");
                }

                // Create trip using manually selected driver
                $trip_id = createTrip(
                    $databaseconn,
                    $driver_id,
                    $plate_num,
                    $warehouse_id,
                    $departure_time
                );

                if (!$trip_id) {
                    throw new Exception("Trip creation failed.");
                }

                // Attach selected jobs
                if (!empty($job_ids)) {

                    $ids = implode(",", array_map('intval', $job_ids));

                    $databaseconn->query("
                UPDATE tbl_job_orders
                SET trip_id = $trip_id,
                    status = 'assigned'
                WHERE id IN ($ids)
            ");
                }

                $databaseconn->commit();

                header("Location: ../views/trips.php");
                exit();
            } catch (Exception $e) {

                $databaseconn->rollback();
                exit($e->getMessage());
            }
            /* =========================
           EDIT TRIP
        ========================== */
        case 'edit':

            $trip_id = $_POST['trip_id'] ?? null;
            $truck_id = $_POST['truck_id'] ?? null;
            $departure_time = $_POST['departure_time'] ?? null;
            $job_ids = $_POST['job_ids'] ?? [];

            if (!$trip_id || !$truck_id) {
                exit("Missing required fields.");
            }

            // Get driver + plate from truck
            $stmt = $databaseconn->prepare("
                SELECT driver_id, plate_num 
                FROM tbl_fleetlist 
                WHERE PK_FLEET = ?
            ");
            $stmt->bind_param("i", $truck_id);
            $stmt->execute();
            $truck = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$truck) {
                exit("Truck not found.");
            }

            $driver_id = $truck['driver_id'];
            $plate_num = $truck['plate_num'];

            // Update trip info
            $stmt = $databaseconn->prepare("
                UPDATE tbl_trips
                SET driver_id = ?,
                    truck_plate_number = ?,
                    departure_time = ?
                WHERE trip_id = ?
            ");
            $stmt->bind_param("issi", $driver_id, $plate_num, $departure_time, $trip_id);
            $stmt->execute();
            $stmt->close();

            // Reset all current jobs in this trip
            $databaseconn->query("
                UPDATE tbl_job_orders
                SET trip_id = NULL,
                    status = 'pending'
                WHERE trip_id = $trip_id
            ");

            // Reassign selected jobs
            if (!empty($job_ids)) {

                $ids = implode(",", array_map('intval', $job_ids));

                $databaseconn->query("
                    UPDATE tbl_job_orders
                    SET trip_id = $trip_id,
                        status = 'assigned'
                    WHERE id IN ($ids)
                ");
            }

            header("Location: ../views/trips.php");
            exit();
    }
}

/* =========================
   LOAD DATA FOR PAGE
========================= */

$drivers = getActiveDrivers($databaseconn);
$warehouses = getActiveWarehouses($databaseconn);

/* =========================
   PAGINATION
========================= */

$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

$totalTrips = countAllTrips($databaseconn);
$totalPages = ceil($totalTrips / $limit);

$all_trips = getTripsPaginated($databaseconn, $limit, $offset);
$active_trips_count = countActiveTrips($databaseconn);
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$unscheduled_jobs_count = getUnscheduledJobOrdersCount($databaseconn);
$available_trucks = getAvailableTrucks($databaseconn);

$available_trucks_count = $available_trucks ? $available_trucks->num_rows : 0;
$pending_jobs_count     = $unscheduled_jobs ? $unscheduled_jobs->num_rows : 0;
