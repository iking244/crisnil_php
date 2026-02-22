<?php
function createJobOrder(
    $conn,
    $origin,
    $origin_lat,
    $origin_lng,
    $destination,
    $destination_lat,
    $destination_lng
) {
    $sql = "INSERT INTO tbl_job_orders 
            (origin, origin_lat, origin_lng, destination, destination_lat, destination_lng, status)
            VALUES 
            ('$origin', '$origin_lat', '$origin_lng', '$destination', '$destination_lat', '$destination_lng', 'pending')";

    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    return false;
}

function addJobOrderItem($conn, $job_order_id, $product_id, $quantity) {
    $sql = "INSERT INTO tbl_job_order_items
            (job_order_id, product_id, quantity)
            VALUES ('$job_order_id', '$product_id', '$quantity')";
    return $conn->query($sql);
}

function deductStock($conn, $warehouse_id, $product_id, $quantity) {
    $sql = "UPDATE tbl_warehouse_stock
            SET quantity = quantity - $quantity
            WHERE warehouse_id = $warehouse_id
            AND product_id = $product_id";
    return $conn->query($sql);
}

function getActiveJobOrders($conn) {
    $sql = "
        SELECT 
            id,
            origin,
            destination,
            status,
            eta
        FROM tbl_job_orders
        WHERE status != 'completed'
        AND status != 'pending'
        ORDER BY id ASC
    ";

    return $conn->query($sql);
}

function getUnscheduledJobOrders($conn) {
    $sql = "
        SELECT 
            id,
            origin,
            destination,
            status
        FROM tbl_job_orders
        WHERE status = 'pending'
        ORDER BY id ASC
    ";

    return $conn->query($sql);
}
function getUnscheduledJobOrdersCount($conn) {
    $sql = "
        SELECT 
            COUNT(*)
        FROM tbl_job_orders
        WHERE status = 'pending'
        ORDER BY id ASC
    ";

    return $conn->query($sql);
}

function getJobsByTrip($conn, $trip_id) {
    $sql = "
        SELECT id, origin, destination, status
        FROM tbl_job_orders
        WHERE trip_id = $trip_id
    ";
    return $conn->query($sql);
}

function assignJobToTrip($conn, $job_id, $trip_id) {
    $sql = "
        UPDATE tbl_job_orders
        SET trip_id = $trip_id,
            status = 'assigned'
        WHERE id = $job_id
    ";
    return $conn->query($sql);
}

function removeJobFromTrip($conn, $job_id) {
    $sql = "
        UPDATE tbl_job_orders
        SET trip_id = NULL,
            status = 'pending'
        WHERE id = $job_id
    ";
    return $conn->query($sql);
}

function getAllJobOrders($conn) {
    $sql = "SELECT id, origin, destination, status, created_at, eta 
            FROM tbl_job_orders 
            ORDER BY created_at DESC";
    return $conn->query($sql);
}

function searchJobOrders($conn, $term) {
    $term = "%$term%";
    $stmt = $conn->prepare("SELECT * FROM tbl_job_orders 
                           WHERE origin LIKE ? OR destination LIKE ? 
                           ORDER BY created_at DESC");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    return $stmt->get_result();
}

?>
