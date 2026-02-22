<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

include "../config/database_conn.php";
include "../models/products_model.php";
require_once '../includes/helpers.php';

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   CURRENT WAREHOUSE
========================= */
$warehouse_id = (int)$_POST['warehouse_id'];

if ($warehouse_id <= 0) {
    die("Invalid warehouse selected.");
}


/* =========================
   HANDLE ACTIONS
========================= */
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action === 'add') {

    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $production_date = $_POST['production_date'];
    $expiration_date = $_POST['expiration_date'];

    // Basic validation
    if ($product_id <= 0 || $quantity <= 0) {
        
        log_activity(
            'add_stock_failed',
            'Invalid input for stock add - Product ID: ' . $product_id . ', Qty: ' . $quantity
        );

        header("Location: ../views/product_management.php?error=invalid_input");
        exit;
    }

    // Add stock batch
    $result = addStockBatch(
        $databaseconn,
        $warehouse_id,
        $product_id,
        $quantity,
        $production_date,
        $expiration_date
    );

    if ($result) {  // assuming addStockBatch() returns true/false or similar
        log_activity(
            'add_stock',
            'Added stock: ' . $quantity . ' units to Product ID ' . $product_id . 
            ' in Warehouse ' . $warehouse_id . 
            ' (Prod date: ' . $production_date . ', Exp: ' . $expiration_date . ')'
        );
        header("Location: ../views/product_management.php?warehouse_id=" . $warehouse_id);
        exit;
    } else {
        log_activity(
            'add_stock_failed',
            'Failed to add stock for Product ID ' . $product_id . 
            ' - Error: ' . mysqli_error($databaseconn)
        );
        die("Stock insert failed: " . mysqli_error($databaseconn));
    }

    // Redirect back to product page
    header("Location: ../views/product_management.php?warehouse_id=" . $warehouse_id);
    exit;
}
