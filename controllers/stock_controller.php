<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

include "../config/database_conn.php";
include "../models/products_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   CURRENT WAREHOUSE
========================= */
$warehouse_id = $_SESSION['warehouse_id'] ?? 1;

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

    if (!$result) {
        die("Stock insert failed: " . mysqli_error($databaseconn));
    }

    // Redirect back to product page
    header("Location: ../views/product_management.php?warehouse_id=" . $warehouse_id);
    exit;
}
