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

$receivingItems = mysqli_query($databaseconn, "
SELECT 
    dr.dr_number,
    di.delivery_item_id,
    p.product_name,
    di.qty AS expected_boxes,

    COUNT(CASE WHEN sb.box_weight > 0 THEN 1 END) AS received_boxes,

    (di.qty - COUNT(CASE WHEN sb.box_weight > 0 THEN 1 END)) AS remaining_boxes

FROM tbl_delivery_items di

JOIN tbl_delivery_receipts dr 
ON di.delivery_receipt_id = dr.delivery_receipt_id

JOIN tbl_products p 
ON di.product_id = p.product_id

LEFT JOIN tbl_stock_boxes sb
ON sb.delivery_item_id = di.delivery_item_id

GROUP BY di.delivery_item_id

HAVING remaining_boxes > 0

ORDER BY dr.dr_number DESC
");

$pallets = mysqli_query($databaseconn, "
SELECT pallet_id,pallet_code
FROM tbl_pallets
WHERE status='active'
ORDER BY pallet_code
");


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
