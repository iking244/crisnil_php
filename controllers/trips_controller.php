<?php
session_start();
include "../config/database_conn.php";
include "../models/trips_model.php";
include "../models/job_order_model.php";
require_once '../includes/helpers.php';

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

function countActiveTrips($conn) {
    $sql = "SELECT COUNT(*) as total 
            FROM tbl_trips 
            WHERE status IN ('assigned', 'in_transit', 'loading', 'pending_loading', 'ready_to_depart')";
    
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
    return 0;
}

/* =========================
   HANDLE TRIP CREATION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $truck_id = $_POST['truck_id'];
    $warehouse_id = $_POST['warehouse_id'];

    // Get driver automatically from truck
    $sql = "SELECT driver_id, plate_num FROM tbl_fleetlist WHERE PK_FLEET = $truck_id";
    $result = $databaseconn->query($sql);
    $truck = $result->fetch_assoc();

    if (!$truck) {
        echo "Truck not found.";
        exit();
    }

    $driver_id = $truck['driver_id'];
    $plate_num = $truck['plate_num'];

    // Create trip
    $trip_id = createTrip(
        $databaseconn,
        $driver_id,
        $plate_num,
        $warehouse_id
    );

    if ($trip_id) {
        log_activity(
            'create_trip',
            'Successfully created trip ID ' . $trip_id .
            ' with truck ' . $plate_num . ' (Driver ID: ' . $driver_id . ')' .
            ' from warehouse ' . $warehouse_id
        );
        header("Location: ../views/trips.php");
        exit();
    } else {
        log_activity(
            'create_trip_failed',
            'createTrip() failed for truck ID ' . $truck_id .
            ' (Warehouse: ' . $warehouse_id . ')'
        );
        echo "Error creating trip.";
        exit();
    }
}

/* =========================
   LOAD DATA FOR FORM
========================= */
$drivers = getActiveDrivers($databaseconn);
$warehouses = getActiveWarehouses($databaseconn);
/* =========================
   PAGINATION FOR TRIPS
========================= */
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$totalTrips = countAllTrips($databaseconn);
$totalPages = ceil($totalTrips / $limit);

$all_trips = getTripsPaginated($databaseconn, $limit, $offset);
$active_trips_count = countActiveTrips($databaseconn);
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$available_trucks = getAvailableTrucks($databaseconn);

$available_trucks_count = $available_trucks ? $available_trucks->num_rows : 0;
$pending_jobs_count     = $unscheduled_jobs ? $unscheduled_jobs->num_rows : 0;