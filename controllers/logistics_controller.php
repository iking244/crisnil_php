<?php
session_start();
include "../config/database_conn.php";
include "../models/logistics_model.php";
include "../models/job_order_model.php";
include "../models/trips_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

// Get stats
$stats = getLogisticsStats($databaseconn);
$job_orders = getActiveJobOrders($databaseconn);
$unscheduled_jobs = getUnscheduledJobOrders($databaseconn);
$active_trips = getActiveTrips($databaseconn);
$intransit_trips = getInTransitTrips($databaseconn);
$route_health = getRouteHealthStats($databaseconn);