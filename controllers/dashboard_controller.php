<?php
session_start();
include "../config/database_conn.php";
include "../models/dashboard_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

$total_products = getTotalProducts($databaseconn);
$product_distribution = getProductDistribution($databaseconn);
$low_stock = getLowStockCount($databaseconn);
$active_deliveries = getActiveDeliveries($databaseconn);
$delivery_distribution = getDeliveryDistribution($databaseconn);
$activity_result = getRecentActivity($databaseconn);
$today_notifications = getTodayNotifications($databaseconn);
