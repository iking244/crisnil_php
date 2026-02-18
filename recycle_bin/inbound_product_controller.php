<?php
session_start();
include "../config/database_conn.php";
include "../models/inbound_product_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['USER_ID'];

/* Get user info */
$user = getUserData($databaseconn, $userId);
$username = $user['USER_NAME'];
$userrole = $user['USER_ROLE'];

/* Get inventory summary */
$summary = getInventorySummary($databaseconn);
$return_availqty = $summary['total_qty'];
$return_totalboxcost = $summary['total_cost'];

/* Get inventory items */
$inventory_items = getInventoryList($databaseconn);

/* Get notifications */
$today_notifications = getTodayNotifications($databaseconn);
