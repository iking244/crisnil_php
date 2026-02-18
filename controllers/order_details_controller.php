<?php
// controllers/order_details_new_controller.php

session_start();
include "../config/database_conn.php";
include "../models/order_details_new_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    die("Invalid order ID");
}

// Fetch main order + trip + driver + truck
$order = getOrderDetailsWithTrip($databaseconn, $order_id);

if (!$order) {
    die("Order #$order_id not found or query failed.");
}

// IMPORTANT: Fetch products separately and add to $order
$order['products'] = getOrderProducts($databaseconn, $order_id);

?>