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

        $query = "
SELECT 
dr.warehouse_id,
di.product_id
FROM tbl_delivery_items di
JOIN tbl_delivery_receipts dr
ON dr.delivery_receipt_id = di.delivery_receipt_id
WHERE di.delivery_item_id = ?
";
        $stmt = $databaseconn->prepare($query);
        $stmt->bind_param("i", $delivery_item_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $warehouse_id = $row['warehouse_id'];
        $product_id = $row['product_id'];


        insertBoxes(
            $databaseconn,
            $delivery_item_id,
            $warehouse_id,
            $product_id,
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
