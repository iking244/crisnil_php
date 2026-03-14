<?php
function getActiveWarehouses($databaseconn)
{
    $sql = "SELECT * FROM tbl_warehouses WHERE is_active = 1";
    return $databaseconn->query($sql);
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
    $pallet_id,
    $box_ids,
    $weights,
    $sizes,
    $batches,
    $pallets,
    $expiries
) {

    foreach ($weights as $i => $weight) {

        $box_id = $box_ids[$i];
        $size = $sizes[$i];
        $batch = $batches[$i];
        $expiry = $expiries[$i];
        $pallet_code = $pallets[$i];

        /* skip empty rows */

        if (
            empty($weight) &&
            empty($batch) &&
            empty($expiry)
        ) {
            continue;
        }

        if ($box_id) {

            $stmt = mysqli_prepare($databaseconn, "
        UPDATE tbl_stock_boxes
        SET box_weight=?, box_size=?, batch_code=?, expiry_date=?, pallet_code=?,pallet_id=?
        WHERE box_id=?
        ");

            mysqli_stmt_bind_param(
                $stmt,
                "dssssii",
                $weight,
                $size,
                $batch,
                $expiry,
                $pallet_code,
                $pallet_id,
                $box_id
            );

            mysqli_stmt_execute($stmt);
        } else {

            $stmt = mysqli_prepare($databaseconn, "
        INSERT INTO tbl_stock_boxes
        (delivery_item_id, warehouse_id, product_id, box_weight, box_size, batch_code, pallet_code, expiry_date,pallet_id)
        VALUES (?,?,?,?,?,?,?,?,?)
        ");

            mysqli_stmt_bind_param(
                $stmt,
                "iiidssssi",
                $delivery_item_id,
                $warehouse_id,
                $product_id,
                $weight,
                $size,
                $batch,
                $pallet_code,
                $expiry,
                $pallet_id
            );

            mysqli_stmt_execute($stmt);
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

function getPendingDeliveryItems($conn)
{
    $query = "
        SELECT COUNT(*) AS pending_items
        FROM (
            SELECT 
                di.delivery_item_id,
                di.qty,
                COUNT(CASE WHEN sb.box_weight > 0 THEN 1 END) AS received_boxes
            FROM tbl_delivery_items di
            LEFT JOIN tbl_stock_boxes sb
                ON sb.delivery_item_id = di.delivery_item_id
            GROUP BY di.delivery_item_id
            HAVING (di.qty - COUNT(CASE WHEN sb.box_weight > 0 THEN 1 END)) > 0
        ) pending
    ";

    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['pending_items'] ?? 0;
}



function getBoxesPending($databaseconn)
{
    $query = "
        SELECT SUM(qty - received_boxes) AS boxes_pending
        FROM (
            SELECT 
                di.delivery_item_id,
                di.qty,
                COUNT(sb.box_id) AS received_boxes
            FROM tbl_delivery_items di
            LEFT JOIN tbl_stock_boxes sb
                ON sb.delivery_item_id = di.delivery_item_id
            GROUP BY di.delivery_item_id
        ) t
        WHERE received_boxes < qty
    ";

    $result = mysqli_query($databaseconn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['boxes_pending'] ?? 0;
}


function getActivePallets($databaseconn)
{
    $query = "
        SELECT COUNT(*) AS active_pallets
        FROM tbl_pallets
        WHERE status = 'active'
    ";

    $result = mysqli_query($databaseconn, $query);
    return mysqli_fetch_assoc($result)['active_pallets'] ?? 0;
}


function getReceivedToday($databaseconn)
{
    $query = "
        SELECT COUNT(*) AS received_today
        FROM tbl_stock_boxes
        WHERE created_at >= CURDATE()
    ";

    $result = mysqli_query($databaseconn, $query);
    $row = mysqli_fetch_assoc($result);

    return $row['received_today'] ?? 0;
}

function getReceivingItems($databaseconn)
{
    $query = "
        SELECT 
            dr.dr_number,
            di.delivery_item_id,
            p.product_name,
            di.qty AS expected_boxes,
            di.missing_boxes,
            di.damaged_boxes,
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

        HAVING (di.qty - COUNT(CASE WHEN sb.box_weight > 0 THEN 1 END)) > 0

        ORDER BY dr.dr_number DESC
    ";

    return mysqli_query($databaseconn, $query);
}


function getActivePalletList($databaseconn)
{
    $query = "
        SELECT pallet_id, pallet_code
        FROM tbl_pallets
        WHERE status='active'
        ORDER BY pallet_code
    ";

    return mysqli_query($databaseconn, $query);
}

function getDeliveryItemInfo($databaseconn, $delivery_item_id)
{
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

    return $result->fetch_assoc();
}

function getPalletCapacity($conn)
{
    $query = "
        SELECT 
            p.pallet_id,
            p.pallet_code,
            COUNT(sb.box_id) AS box_count,
            (COUNT(sb.box_id) / 60 * 100) AS percent_used
        FROM tbl_pallets p
        LEFT JOIN tbl_stock_boxes sb
            ON sb.pallet_id = p.pallet_id
        WHERE p.status = 'active'
        GROUP BY p.pallet_id
        ORDER BY p.pallet_code
    ";

    return mysqli_query($conn, $query);
}