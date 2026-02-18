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
// fallback to 1 if not set

/* =========================
   HANDLE ACTIONS
========================= */
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action) {

    /* CREATE PRODUCT */
    if ($action === 'create') {

        $code = $_POST['product_code'];
        $name = $_POST['product_name'];
        $unit_id = (int)$_POST['unit_id'];
        $qty = (int)$_POST['quantity'];
        $weight_per_unit = (float)$_POST['weight_per_unit'];
        $units_per_pallet = (int)$_POST['units_per_pallet'];

        createProduct(
            $databaseconn,
            $warehouse_id,
            $code,
            $name,
            $unit_id,
            $qty,
            $weight_per_unit,
            $units_per_pallet
        );

        header("Location: ../views/product_management.php");
        exit;
    }

    /* UPDATE PRODUCT */
    if ($action === 'update') {

        $id = (int)$_POST['product_id'];
        $code = $_POST['product_code'];
        $name = $_POST['product_name'];
        $unit_id = (int)$_POST['unit_id'];
        $qty = (int)$_POST['quantity'];
        $weight_per_unit = (float)$_POST['weight_per_unit'];
        $units_per_pallet = (int)$_POST['units_per_pallet'];

        updateProduct(
            $databaseconn,
            $warehouse_id,
            $id,
            $code,
            $name,
            $unit_id,
            $qty,
            $weight_per_unit,
            $units_per_pallet
        );

        header("Location: ../views/product_management.php");
        exit;
    }
}

$units = mysqli_query($databaseconn, "SELECT unit_id, unit_name FROM tbl_units ORDER BY unit_name");
/* =========================
   PAGINATION
========================= */
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

$totalProducts = countAllProducts($databaseconn);
$totalPages = ceil($totalProducts / $limit);

/* =========================
   LOAD PRODUCTS
========================= */
$products = getProductsPaginated($databaseconn, $warehouse_id, $limit, $offset);
