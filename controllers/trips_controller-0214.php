<?php
session_start();
include "../config/database_conn.php";
include "../models/trips_model.php";
include "../models/job_order_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
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
        header("Location: ../views/trips.php");
        exit();
    } else {
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
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$available_trucks = getAvailableTrucks($databaseconn);
