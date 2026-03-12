<?php
function getActiveWarehouses($conn)
{
    $sql = "SELECT * FROM tbl_warehouses WHERE is_active = 1";
    return $conn->query($sql);
}

function getDeliveryItemsForAssignment($databaseconn)
{

    $query = "

SELECT 
    di.delivery_item_id,
    dr.dr_number,
    p.product_name,
    di.product_id,
    di.qty,
    di.total_weight,

    COUNT(sb.box_id) AS assigned_boxes

FROM tbl_delivery_items di

JOIN tbl_delivery_receipts dr
ON dr.delivery_receipt_id = di.delivery_receipt_id

JOIN tbl_products p
ON p.product_id = di.product_id

LEFT JOIN tbl_stock_boxes sb
ON sb.delivery_item_id = di.delivery_item_id

GROUP BY di.delivery_item_id

";

    return mysqli_query($databaseconn, $query);
}


function insertBoxes($databaseconn, $delivery_item_id, $warehouse_id, $product_id, $weights, $sizes, $batches, $pallets, $expiries)
{

    $query = "
INSERT INTO tbl_stock_boxes
(
delivery_item_id,
warehouse_id,
product_id,
box_weight,
box_size,
batch_code,
pallet_code,
expiry_date
)
VALUES (?,?,?,?,?,?,?,?)
";

    $stmt = $databaseconn->prepare($query);

    for ($i = 0; $i < count($weights); $i++) {

        $weight = $weights[$i];
        $size = $sizes[$i];
        $batch = $batches[$i];
        $pallet = $pallets[$i];
        $expiry = $expiries[$i];

        $stmt->bind_param(
            "iiidssss",
            $delivery_item_id,
            $warehouse_id,
            $product_id,
            $weight,
            $size,
            $batch,
            $pallet,
            $expiry
        );

        $stmt->execute();
    }
}
