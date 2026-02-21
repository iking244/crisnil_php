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

function getRouteHealthStats($conn)
{
    $stats = [];

    // In Transit
    $result = $conn->query("
        SELECT COUNT(*) as total 
        FROM tbl_trips 
        WHERE status = 'in_transit'
    ");
    $stats['in_transit'] = $result->fetch_assoc()['total'];

    // Scheduled
    $result = $conn->query("
        SELECT COUNT(*) as total 
        FROM tbl_trips 
        WHERE status IN ('pending_loading', 'loading')
    ");
    $stats['scheduled'] = $result->fetch_assoc()['total'];

    // Completed Today
      $result = $conn->query("
           SELECT COUNT(*) as total 
           FROM tbl_trips 
           WHERE status = 'completed'
          AND DATE(completed_at) = CURDATE()
       ");
     $stats['completed_today'] = $result->fetch_assoc()['total'];

    // Arrived (optional if you have this status)
   // $result = $conn->query("
     //   SELECT COUNT(*) as total 
    //    FROM tbl_trips 
   //     WHERE status = 'arrived'
  //  ");
  //  $stats['arrived'] = $result->fetch_assoc()['total'];

    return $stats;
}
