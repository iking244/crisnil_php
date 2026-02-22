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
                p.PROD_TYPE_LIST,
                SUM(i.QUANTITY) AS CURR_STOCK,
                p.MIN_STOCK_LEVEL
            FROM prod_type_table p
            LEFT JOIN inbounditems_table i 
                ON i.PROD_TYPE = p.PROD_TYPE_LIST
                AND i.INV_STATUS = 'On Hand'
            GROUP BY p.PROD_TYPE_LIST
            HAVING CURR_STOCK < MIN_STOCK_LEVEL
        ) AS low_items
    ");

    $row = mysqli_fetch_assoc($query);
    return $row['total'] ?? 0;
}

function getActiveDeliveries($conn) {
    $query = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM tbl_job_orders 
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
