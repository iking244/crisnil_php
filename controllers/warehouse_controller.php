<?php

session_start();

include "../config/database_conn.php";
include "../models/warehouses_model.php";

if (!isset($_SESSION['USER_ID'])) {

    header("Location: ../index.php");
    exit();
}

# --------------------------------
# LOAD DATA FOR VIEW
# --------------------------------

$deliveryItems = getDeliveryItemsForAssignment($databaseconn);


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

        insertBoxes(
            $databaseconn,
            $delivery_item_id,
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
