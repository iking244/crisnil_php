<?php

function getTotalProducts($conn) {
    $query = mysqli_query($conn, "
        SELECT COUNT(*) AS total 
        FROM prod_type_table
    ");
    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}

function getProductDistribution($conn) {
    $data = [];
    $total_stock = 0;

    $query = mysqli_query($conn, "
        SELECT 
            prod_type_table.PROD_TYPE_LIST AS product,
            SUM(inbounditems_table.QUANTITY) AS qty
        FROM inbounditems_table
        RIGHT JOIN prod_type_table 
            ON inbounditems_table.PROD_TYPE = prod_type_table.PROD_TYPE_LIST
        WHERE inbounditems_table.INV_STATUS = 'On Hand'
        GROUP BY prod_type_table.PROD_TYPE_LIST
        ORDER BY qty DESC
        LIMIT 3
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
        $total_stock += $row['qty'];
    }

    foreach ($data as &$prod) {
        $percent = $total_stock > 0
            ? ($prod['qty'] / $total_stock) * 100
            : 0;

        $prod['percent'] = $percent;

        if ($percent <= 20) {
            $prod['color'] = '#e53935';
        } elseif ($percent <= 50) {
            $prod['color'] = '#fbc02d';
        } else {
            $prod['color'] = '#43a047';
        }
    }

    return $data;
}

function getLowStockCount($conn) {
    $query = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM (
            SELECT 
                p.product_id,
                SUM(s.quantity_remaining) AS stock,
                p.min_stock_level
            FROM tbl_products p
            LEFT JOIN tbl_stock_boxes s 
                ON s.product_id = p.product_id
            GROUP BY p.product_id
            HAVING stock < p.min_stock_level
        ) AS low_items
    ");

    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}

function getActiveDeliveries($conn) {
    $query = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM tbl_delivery_receipts
        WHERE status = 'in_transit'
    ");
    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}

function getDeliveryDistribution($conn) {
    $data = [];
    $total = 0;

    $query = mysqli_query($conn, "
        SELECT 
            track_status AS status,
            COUNT(*) AS count
        FROM tbl_tracking
        WHERE track_status != 'DELIVERED'
        GROUP BY track_status
        ORDER BY count DESC
        LIMIT 3
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
        $total += $row['count'];
    }

    foreach ($data as &$del) {
        $percent = $total > 0
            ? ($del['count'] / $total) * 100
            : 0;

        $del['percent'] = $percent;

        if ($percent <= 20) {
            $del['color'] = '#e53935';
        } elseif ($percent <= 50) {
            $del['color'] = '#fbc02d';
        } else {
            $del['color'] = '#43a047';
        }
    }

    return $data;
}

function getRecentActivity($conn) {
    return mysqli_query($conn, "
        SELECT notif_title, notif_desc, notif_time
        FROM tbl_notif
        ORDER BY notif_time DESC
        LIMIT 3
    ");
}


function getTodayNotifications($conn) {
    return mysqli_query($conn, "
        SELECT DISTINCT notif_title, notif_desc, notif_time
        FROM tbl_notif
        WHERE notif_time = CURRENT_DATE
    ");
}

function getStockMovement($conn) {

    // STOCK IN (total weight from stock boxes)
    $stockInQuery = mysqli_query($conn, "
        SELECT SUM(box_weight) AS total_in
        FROM tbl_stock_boxes
    ");
    $stockInRow = mysqli_fetch_assoc($stockInQuery);
    $stock_in = $stockInRow['total_in'] ?? 0;

    // STOCK OUT (total weight delivered)
    $stockOutQuery = mysqli_query($conn, "
        SELECT SUM(total_weight) AS total_out
        FROM tbl_delivery_items
    ");
    $stockOutRow = mysqli_fetch_assoc($stockOutQuery);
    $stock_out = $stockOutRow['total_out'] ?? 0;

    return [
        'stock_in' => (int)$stock_in,
        'stock_out' => (int)$stock_out
    ];
}

function getSalesTrendFromDeliveries($conn) {

    $data = [];
    $labels = [];

    $query = mysqli_query($conn, "
        SELECT 
            DATE(created_at) as sale_date,
            SUM(total_amount) as total
        FROM tbl_delivery_receipts
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY sale_date ASC
    ");

    $results = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $results[$row['sale_date']] = (float)$row['total'];
    }

    // Build last 7 days (including days with 0)
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('D', strtotime($date)); // Tue, Wed, etc
        $data[] = $results[$date] ?? 0;
    }

    return [
        'labels' => $labels,
        'data' => $data
    ];
}

function getLowStockItems($conn) {
    return mysqli_query($conn, "
        SELECT 
            p.product_name,
            SUM(s.box_weight) AS stock,
            p.min_stock_level
        FROM tbl_products p
        LEFT JOIN tbl_stock_boxes s 
            ON s.product_id = p.product_id
            AND s.status = 'available'
        GROUP BY p.product_id
        HAVING stock < p.min_stock_level
        ORDER BY stock ASC
        LIMIT 5
    ");
}

function getRecentDeliveries($conn) {
    return mysqli_query($conn, "
        SELECT 
            delivery_receipt_id,
            status,
            created_at
        FROM tbl_delivery_receipts
        ORDER BY created_at DESC
        LIMIT 5
    ");
}