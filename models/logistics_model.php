<?php

function getLogisticsStats($conn)
{
    $stats = [
        'active_routes' => 0,
        'orders_today' => 0,
        'delayed' => 0
    ];

    // Active routes
    $query = "SELECT COUNT(*) as total
FROM tbl_trips
WHERE status = 'in_transit';
";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['active_routes'] = $row['total'];
    }

    // Orders today
    $query = "SELECT COUNT(*) AS total 
              FROM tbl_job_orders 
              WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['orders_today'] = $row['total'];
    }

    // Delayed
    $query = "SELECT COUNT(*) AS total 
              FROM tbl_tracking 
              WHERE track_status = 'Delayed'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $stats['delayed'] = $row['total'];
    }

    return $stats;
}


