<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();
include "../config/database_conn.php";
include "../models/trips_model.php";
include "../models/job_order_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

$trip_id = $_GET['trip_id'] ?? $_POST['trip_id'] ?? null;

/* UPDATE TRIP INFO */
if (isset($_POST['update_trip'])) {
    $driver_id = $_POST['driver_id'];
    $truck_plate = $_POST['truck_plate_number'];

    updateTripInfo($databaseconn, $trip_id, $driver_id, $truck_plate);
    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* ADD JOB */
if (isset($_GET['add_job'])) {
    $job_id = $_GET['add_job'];
    assignJobToTrip($databaseconn, $job_id, $trip_id);
    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* REMOVE JOB */
if (isset($_GET['remove_job'])) {
    $job_id = $_GET['remove_job'];
    removeJobFromTrip($databaseconn, $job_id);
    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* LOAD DATA */
$trip = getTripById($databaseconn, $trip_id);
$trip_jobs = getJobsByTrip($databaseconn, $trip_id);
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$drivers = getActiveDrivers($databaseconn);
