<?php
session_start();
include "../config/database_conn.php";
include "../models/products_model.php";
include "../models/job_order_model.php";
include "../models/warehouses_model.php";
include "../models/clients_model.php";
include "../models/drivers_model.php";



if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   HANDLE FORM SUBMISSION
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $warehouse_id = $_POST['warehouse_id'];
    $client_id = $_POST['client_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    /* =========================
   GET WAREHOUSE (ORIGIN)
========================= */
    $sql = "SELECT * FROM tbl_warehouses WHERE warehouse_id = $warehouse_id";
    $result = $databaseconn->query($sql);
    $warehouse = $result->fetch_assoc();

    if (!$warehouse) {
        echo "Invalid warehouse selected.";
        exit();
    }

    $origin = $warehouse['warehouse_name'];
    $origin_lat = $warehouse['latitude'];
    $origin_lng = $warehouse['longitude'];

    /* =========================
   GET CLIENT (DESTINATION)
========================= */
    $sql = "SELECT * FROM tbl_clients WHERE client_id = $client_id";
    $result = $databaseconn->query($sql);
    $client = $result->fetch_assoc();

    if (!$client) {
        echo "Invalid client selected.";
        exit();
    }

    $destination = $client['client_name'];
    $destination_lat = $client['latitude'];
    $destination_lng = $client['longitude'];

    /* =========================
   CREATE JOB ORDER
========================= */
    $job_order_id = createJobOrder(
        $databaseconn,
        $origin,
        $origin_lat,
        $origin_lng,
        $destination,
        $destination_lat,
        $destination_lng,
    );


    if ($job_order_id) {

        // Add cargo
        addJobOrderItem(
            $databaseconn,
            $job_order_id,
            $product_id,
            $quantity
        );

        // Deduct stock
        deductStock(
            $databaseconn,
            $warehouse_id,
            $product_id,
            $quantity
        );

        header("Location: ../views/logistics_orders.php?success=1");
        exit();
    } else {
        echo "Error creating job order.";
        exit();
    }
}


/* =========================
   LOAD DATA FOR FORM
========================= */
$products = getActiveProducts($databaseconn);
$warehouses = getActiveWarehouses($databaseconn);
$clients = getActiveClients($databaseconn);
