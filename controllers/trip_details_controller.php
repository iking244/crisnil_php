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
    
    log_activity(
        'update_trip_attempt',
        'Attempted to update trip ID ' . $trip_id .
        ' - New driver ID: ' . $driver_id .
        ', New truck plate: ' . $truck_plate
    );

    updateTripInfo($databaseconn, $trip_id, $driver_id, $truck_plate);
    
    log_activity(
        'update_trip',
        'Updated trip ID ' . $trip_id .
        ' - New driver ID: ' . $driver_id .
        ', New truck plate: ' . $truck_plate
    );
    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* ADD JOB */
if (isset($_GET['add_job'])) {
    $job_id  = (int)$_GET['add_job'];
    $trip_id = (int)$trip_id;  // already set earlier in the file

    // Early log attempt
    log_activity(
        'assign_job_attempt',
        'Attempted to assign job ID ' . $job_id . ' to trip ID ' . $trip_id
    );

    if ($job_id <= 0 || $trip_id <= 0) {
        log_activity(
            'assign_job_failed',
            'Failed to assign job - Invalid IDs (Job: ' . $job_id . ', Trip: ' . $trip_id . ')'
        );
        header("Location: ../views/trip_details.php?trip_id=$trip_id&error=invalid_ids");
        exit();
    }

    // Perform the assignment
    assignJobToTrip($databaseconn, $job_id, $trip_id);

    // Log success (assuming the function succeeds if no error thrown)
    log_activity(
        'assign_job',
        'Assigned job ID ' . $job_id . ' to trip ID ' . $trip_id
    );

    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* REMOVE JOB */
if (isset($_GET['remove_job'])) {
    $job_id  = (int)$_GET['remove_job'];
    $trip_id = (int)$trip_id;

    // Early log attempt
    log_activity(
        'remove_job_attempt',
        'Attempted to remove job ID ' . $job_id . ' from trip ID ' . $trip_id
    );

    if ($job_id <= 0 || $trip_id <= 0) {
        log_activity(
            'remove_job_failed',
            'Failed to remove job - Invalid IDs (Job: ' . $job_id . ', Trip: ' . $trip_id . ')'
        );
        header("Location: ../views/trip_details.php?trip_id=$trip_id&error=invalid_ids");
        exit();
    }

    // Perform the removal
    removeJobFromTrip($databaseconn, $job_id);

    // Log success
    log_activity(
        'remove_job',
        'Removed job ID ' . $job_id . ' from trip ID ' . $trip_id
    );

    header("Location: ../views/trip_details.php?trip_id=$trip_id");
    exit();
}

/* LOAD DATA */
$trip = getTripById($databaseconn, $trip_id);
$trip_jobs = getJobsByTrip($databaseconn, $trip_id);
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$drivers = getActiveDrivers($databaseconn);
