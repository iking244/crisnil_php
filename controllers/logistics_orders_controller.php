<?php
 session_start();
include "../config/database_conn.php";
include "../models/logistics_orders_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   HANDLE ACTIONS
========================= */
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action) {

    /* =========================
       GET ITEMS (AJAX)
    ========================= */
    if ($action === 'get_items') {
        $job_id = (int)$_GET['id'];
        $items = getLogisticsOrderItems($databaseconn, $job_id);

        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }

    /* =========================
       GET STOCK (AJAX)
    ========================= */
    if ($action === 'get_stock') {
        $product_id = (int)$_GET['product_id'];

        $query = mysqli_query($databaseconn, "
            SELECT quantity
            FROM tbl_warehouse_stock
            WHERE product_id = $product_id
            LIMIT 1
        ");

        $stock = mysqli_fetch_assoc($query);

        header('Content-Type: application/json');
        echo json_encode($stock ?: ['quantity' => 0]);
        exit;
    }

    /* =========================
       UPDATE ORDER
    ========================= */
    if ($action === 'update') {

        $job_id = (int)$_POST['id'];
        $new_status = $_POST['status'];
        $eta = $_POST['eta'];

        $product_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        // Get current status
        $statusQuery = mysqli_query($databaseconn, "
            SELECT status 
            FROM tbl_job_orders 
            WHERE id = $job_id
        ");
        $current = mysqli_fetch_assoc($statusQuery);

        // Safety check
        if (!$current) {
            header("Location: ../views/logistics_orders.php");
            exit;
        }

        $current_status = $current['status'];

        // Prevent edits if already in transit or completed
        if ($current_status === 'in_transit' || $current_status === 'completed') {
            header("Location: ../views/logistics_orders.php");
            exit;
        }

        // Admin can only cancel pending orders
        if ($current_status === 'pending' && $new_status === 'cancelled') {
            $status = 'cancelled';
        } else {
            $status = $current_status;
        }

        // Prevent edits if stock already reserved
        if (hasStockReservation($databaseconn, $job_id)) {
            header("Location: ../views/logistics_orders.php?error=reserved_stock");
            exit;
        }

        updateLogisticsOrder($databaseconn, $job_id, $status, $eta);
        replaceLogisticsOrderItems($databaseconn, $job_id, $product_ids, $quantities);

        header("Location: ../views/logistics_orders.php");
        exit;
    }

    /* =========================
       CREATE ORDER
    ========================= */
    if ($action === 'create') {
        $_SESSION['ERROR'] = "Insufficient stock for one or more products.";
        $warehouse_id = (int)$_POST['warehouse_id'];
        $client_id = (int)$_POST['client_id'];
        $product_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        

        // Check stock first
    if (!checkStockAvailability($databaseconn, $warehouse_id, $product_ids, $quantities)) {
        $_SESSION['error'] = "Insufficient stock for one or more products.";
        header("Location: ../views/logistics_orders.php");
        exit;
    }

        createLogisticsOrder(
            $databaseconn,
            $warehouse_id,
            $client_id,
            $product_ids,
            $quantities
        );


        header("Location: ../views/logistics_orders.php");
        exit;
    }

    /* =========================
   CANCEL OR DELETE ORDER
========================= */
    if ($action === 'delete') {

        $job_id = (int)$_GET['id'];

        // Get current status
        $statusQuery = mysqli_query($databaseconn, "
        SELECT status 
        FROM tbl_job_orders 
        WHERE id = $job_id
    ");
        $row = mysqli_fetch_assoc($statusQuery);

        if (!$row) {
            header("Location: ../views/logistics_orders.php");
            exit;
        }

        $status = $row['status'];

        // If pending or assigned → cancel only
        if ($status === 'pending' || $status === 'assigned') {

            mysqli_query($databaseconn, "
            UPDATE tbl_job_orders
            SET 
                status = 'cancelled',
                trip_id = NULL
            WHERE id = $job_id
        ");

            header("Location: ../views/logistics_orders.php?msg=order_cancelled");
            exit;
        }

        // If already cancelled → delete
        if ($status === 'cancelled') {

            mysqli_query($databaseconn, "
            DELETE FROM tbl_job_orders
            WHERE id = $job_id
        ");

            header("Location: ../views/logistics_orders.php?msg=order_deleted");
            exit;
        }

        // If in transit or completed
        header("Location: ../views/logistics_orders.php?error=delete_not_allowed");
        exit;
    }
}

/* =========================
   PAGINATION
========================= */
$limit = 10; // orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

// total orders
$totalOrders = countAllJobOrders($databaseconn);
$totalPages = ceil($totalOrders / $limit);

/* =========================
   LOAD DATA FOR VIEW
========================= */
$orders = getJobOrdersPaginated($databaseconn, $limit, $offset);
$warehouses = getActiveWarehouses($databaseconn);
$clients = getActiveClients($databaseconn);
$products = getActiveProducts($databaseconn);
