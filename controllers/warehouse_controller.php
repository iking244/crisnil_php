<?php

session_start();

include "../config/database_conn.php";
include "../models/warehouses_model.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

# --------------------------------
# LOAD DATA FOR VIEW
# --------------------------------

$deliveryItems = getDeliveryItemsForAssignment($databaseconn);

$receivingItems = getReceivingItems($databaseconn);

$pallets = getActivePalletList($databaseconn);

$pendingItems = getPendingDeliveryItems($databaseconn);

$boxesPending = getBoxesPending($databaseconn);

$activePallets = getActivePallets($databaseconn);

$receivedToday = getReceivedToday($databaseconn);

$palletCapacity = getPalletCapacity($databaseconn);


# --------------------------------
# AJAX: GET BOXES
# --------------------------------

if (isset($_GET['action']) && $_GET['action'] == "get_boxes") {

    $delivery_item_id = $_GET['delivery_item_id'];

    $boxes = getBoxesByDeliveryItem($databaseconn, $delivery_item_id);

    header('Content-Type: application/json');
    echo json_encode($boxes);
    exit();
}


# --------------------------------
# SAVE BOX ASSIGNMENT
# --------------------------------

if (isset($_GET['action']) && $_GET['action'] == "assign_boxes") {

    try {

        $databaseconn->begin_transaction();

        $delivery_item_id = $_POST['delivery_item_id'];

        $weights = $_POST['weight'];
        $sizes = $_POST['size'];
        $batches = $_POST['batch'];
        $pallets = $_POST['pallet'];
        $expiries = $_POST['expiry'];
        $box_ids = $_POST['box_id'];
        $pallet_id = $_POST['pallet_id'];

        $info = getDeliveryItemInfo($databaseconn, $delivery_item_id);

        $warehouse_id = $info['warehouse_id'];
        $product_id = $info['product_id'];

        insertBoxes(
            $databaseconn,
            $delivery_item_id,
            $warehouse_id,
            $product_id,
            $pallet_id,
            $box_ids,
            $weights,
            $sizes,
            $batches,
            $pallets,
            $expiries
        );

        $databaseconn->commit();

        echo json_encode([
            "status" => "success"
        ]);
    } catch (Exception $e) {

        $databaseconn->rollback();

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

    exit();
}
