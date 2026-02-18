<?php

/**
 * Fetch main order details + trip + driver + truck
 */
function getOrderDetailsWithTrip($conn, $order_id) {
    $sql = "
        SELECT 
            jo.id AS order_id,
            jo.origin,
            jo.destination,
            jo.origin_lat,
            jo.origin_lng,
            jo.destination_lat,
            jo.destination_lng,
            jo.status AS order_status,
            jo.created_at,
            jo.eta,
            jo.proof_photo,
            jo.trip_id,
            t.driver_id,
            t.truck_plate_number,
            t.status AS trip_status,
            t.current_latitude,
            t.current_longitude,
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS driver_name,
            f.MODEL AS truck_model,
            f.PLATE_NUM AS truck_plate
        FROM tbl_job_orders jo
        LEFT JOIN tbl_trips t ON jo.trip_id = t.trip_id
        LEFT JOIN crisnil_users u ON t.driver_id = u.USER_ID
        LEFT JOIN tbl_fleetlist f ON t.truck_plate_number = f.PLATE_NUM
        WHERE jo.id = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) {
        return null;
    }

    // Temporary placeholder - we will fill 'products' in controller
    $result['products'] = [];

    return $result;
}

/**
 * Fetch products for a specific job order
 */
function getOrderProducts($conn, $order_id) {
    $sql = "
        SELECT 
            p.product_name,
            joi.quantity
        FROM tbl_job_order_items joi
        JOIN tbl_products p ON joi.product_id = p.product_id
        WHERE joi.job_order_id = ?
        ORDER BY p.product_name
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Products prepare failed: " . $conn->error);
        return [];
    }

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    return $products;
}