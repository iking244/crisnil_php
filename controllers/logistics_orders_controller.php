<?php
 session_start();
 error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
include "../config/database_conn.php";
include "../models/logistics_orders_model.php";
require_once '../includes/helpers.php';

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
        
        log_activity(
        'update_order',
        'Updated order ID ' . $job_id .
        ' to status: ' . $status .
        ' (ETA: ' . $eta . ', ' . count($product_ids) . ' items affected)'
        );

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

        log_activity(
        'create_order_attempt',
        'Attempted to create logistics order for client ID ' . $client_id .
        ' from warehouse ' . $warehouse_id .
        ' with ' . count($product_ids) . ' items'
    );
        

        // Check stock first
    if (!checkStockAvailability($databaseconn, $warehouse_id, $product_ids, $quantities)) {
        log_activity(
            'create_order_failed',
            'Failed to create order for client ID ' . $client_id .
            ' - Insufficient stock (warehouse: ' . $warehouse_id . ')'
        );
        $_SESSION['error'] = "Insufficient stock for one or more products.";
        header("Location: ../views/logistics_orders.php");
        exit;
    }

        $order_id = createLogisticsOrder(
            $databaseconn,
            $warehouse_id,
            $client_id,
            $product_ids,
            $quantities
        );
if ($order_id) {
        log_activity(
            'create_order',
            'Created logistics order ID ' . $order_id .
            ' for client ID ' . $client_id .
            ' from warehouse ' . $warehouse_id .
            ' (' . count($product_ids) . ' items)'
        );
        header("Location: ../views/logistics_orders.php");
        exit;
    } else {
        log_activity(
            'create_order_failed',
            'createLogisticsOrder() failed for client ID ' . $client_id .
            ' - No order ID returned'
        );
        $_SESSION['error'] = "Failed to create order.";
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
            
            log_activity('cancel_order', 'Cancelled order ID ' . $job_id . ' (was pending/assigned)');

            header("Location: ../views/logistics_orders.php?msg=order_cancelled");
            exit;
        }

        // If already cancelled → delete
        if ($status === 'cancelled') {

            mysqli_query($databaseconn, "
            DELETE FROM tbl_job_orders
            WHERE id = $job_id
        ");

            log_activity('delete_order', 'Deleted order ID ' . $job_id . ' (was already cancelled)');

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

/* =========================
   OVERVIEW DATA
========================= */

if (basename($_SERVER['PHP_SELF']) === 'logistics_orders_overview.php') {

    $overviewStats = getLogisticsOverviewStats($databaseconn);
    $overviewRecent = getRecentLogisticsOrders($databaseconn);
    $overviewBreakdown = getLogisticsStatusBreakdown($databaseconn);
    $overviewAlerts = getLogisticsOperationalAlerts($databaseconn);
}
