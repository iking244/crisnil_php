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


function insertBoxes(
    $databaseconn,
    $delivery_item_id,
    $warehouse_id,
    $product_id,
    $box_ids,
    $weights,
    $sizes,
    $batches,
    $pallets,
    $expiries
) {

    for ($i = 0; $i < count($weights); $i++) {

        $box_id = $box_ids[$i];
        $weight = $weights[$i];
        $size = $sizes[$i];
        $batch = $batches[$i];
        $pallet = $pallets[$i];
        $expiry = $expiries[$i];

        if ($box_id) {

            $query = "
UPDATE tbl_stock_boxes
SET
box_weight = ?,
box_size = ?,
batch_code = ?,
pallet_code = ?,
expiry_date = ?
WHERE box_id = ?
";

            $stmt = $databaseconn->prepare($query);

            $stmt->bind_param(
                "dssssi",
                $weight,
                $size,
                $batch,
                $pallet,
                $expiry,
                $box_id
            );

            $stmt->execute();
        } else {

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
}

function getBoxesByDeliveryItem($databaseconn, $delivery_item_id)
{
    $query = "
        SELECT 
            box_id,
            box_weight,
            box_size,
            batch_code,
            pallet_code,
            expiry_date
        FROM tbl_stock_boxes
        WHERE delivery_item_id = ?
    ";

    $stmt = $databaseconn->prepare($query);
    $stmt->bind_param("i", $delivery_item_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $boxes = [];

    while ($row = $result->fetch_assoc()) {
        $boxes[] = $row;
    }

    return $boxes;
}
