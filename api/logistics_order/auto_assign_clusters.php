<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once "../../config/database_conn.php";

header('Content-Type: application/json');

function haversine($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) *
        cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

try {

    $databaseconn->begin_transaction();

    // ================================
    // GET PENDING JOBS
    // ================================
    $jobsQuery = $databaseconn->query("
        SELECT *
        FROM tbl_job_orders
        WHERE status = 'pending'
        AND trip_id IS NULL
        ORDER BY created_at ASC
        FOR UPDATE
    ");

    $jobs = $jobsQuery->fetch_all(MYSQLI_ASSOC);

    if (empty($jobs)) {
        throw new Exception("No pending jobs");
    }

    // ================================
    // GET AVAILABLE TRUCKS
    // ================================
    $trucksQuery = $databaseconn->query("
        SELECT f.PLATE_NUM
        FROM tbl_fleetlist f
        LEFT JOIN tbl_trips t
          ON f.PLATE_NUM = t.truck_plate_number
          AND t.status IN ('pending','in_transit','pending_loading')
        WHERE t.trip_id IS NULL
        AND f.FLEET_STATUS = 'ACTIVE'
        FOR UPDATE
    ");

    $trucks = $trucksQuery->fetch_all(MYSQLI_ASSOC);

    if (empty($trucks)) {
        throw new Exception("No available trucks");
    }

    // ================================
    // CLUSTER JOBS
    // ================================
    $radius = 5;
    $maxOrdersPerCluster = 5;
    $clusters = [];

    while (!empty($jobs)) {

        $base = array_shift($jobs);
        $cluster = [$base];

        foreach ($jobs as $key => $job) {

            if (count($cluster) >= $maxOrdersPerCluster) {
                break;
            }

            $dist = haversine(
                $base['destination_lat'],
                $base['destination_lng'],
                $job['destination_lat'],
                $job['destination_lng']
            );

            if ($dist <= $radius) {

                $cluster[] = $job;
                unset($jobs[$key]);
            }
        }

        $clusters[] = $cluster;
        $jobs = array_values($jobs);
    }

    // ================================
    // ROUTING START POINT
    // ================================
    $startLat = 14.6091;
    $startLng = 121.0223;

    $assigned = 0;

    // ================================
    // ASSIGN CLUSTERS TO TRUCKS
    // ================================
    foreach ($clusters as $index => $cluster) {

        if (!isset($trucks[$index])) {
            break;
        }

        $plate = $trucks[$index]['PLATE_NUM'];

        // CREATE TRIP
        $stmt = $databaseconn->prepare("
            INSERT INTO tbl_trips
            (truck_plate_number, status, created_at, warehouse_id)
            VALUES (?, 'pending_loading', NOW(), 1)
        ");

        $stmt->bind_param("s", $plate);

        if (!$stmt->execute()) {
            throw new Exception("Failed to create trip");
        }

        $tripId = $stmt->insert_id;

        // ================================
        // DELIVERY SEQUENCING (Nearest)
        // ================================
        $jobsForRouting = $cluster;
        $sequence = 1;

        $currentLat = $startLat;
        $currentLng = $startLng;

        while (!empty($jobsForRouting)) {

            $nearestIndex = null;
            $nearestDistance = 999999;

            foreach ($jobsForRouting as $key => $job) {

                $dist = haversine(
                    $currentLat,
                    $currentLng,
                    $job['destination_lat'],
                    $job['destination_lng']
                );

                if ($dist < $nearestDistance) {

                    $nearestDistance = $dist;
                    $nearestIndex = $key;
                }
            }

            $nearestJob = $jobsForRouting[$nearestIndex];
            $jobId = $nearestJob['id'];

            $update = $databaseconn->prepare("
                UPDATE tbl_job_orders
                SET trip_id = ?, 
                    status = 'assigned',
                    delivery_sequence = ?
                WHERE id = ?
            ");

            $update->bind_param("iii", $tripId, $sequence, $jobId);

            if (!$update->execute()) {
                throw new Exception("Failed to assign job ID: $jobId");
            }

            $currentLat = $nearestJob['destination_lat'];
            $currentLng = $nearestJob['destination_lng'];

            unset($jobsForRouting[$nearestIndex]);

            $sequence++;
            $assigned++;
        }

        // ================================
        // GENERATE PICKLIST
        // ================================
        generatePickList($databaseconn, $tripId);
    }

    $databaseconn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Auto assignment completed",
        "jobs_assigned" => $assigned,
        "clusters_created" => count($clusters)
    ]);
} catch (Exception $e) {

    $databaseconn->rollback();

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString() // 👈 full call stack
    ]);
}



function generatePickList($conn, $tripId)
{

    $items = $conn->prepare("
        SELECT 
            jo.id AS job_order_id,
            joi.product_id,
            SUM(joi.quantity) AS qty
        FROM tbl_job_orders jo
        JOIN tbl_job_order_items joi
        ON jo.id = joi.job_order_id
        WHERE jo.trip_id = ?
        GROUP BY jo.id, joi.product_id
    ");

    $items->bind_param("i", $tripId);
    $items->execute();
    $result = $items->get_result();

    while ($row = $result->fetch_assoc()) {

        $jobOrderId = $row['job_order_id'];
        $productId = $row['product_id'];
        $qtyNeeded = $row['qty'];

        // FEFO box selection
        $boxes = $conn->prepare("
            SELECT box_id, pallet_id
            FROM tbl_stock_boxes
            WHERE product_id = ?
            AND status = 'available'
            ORDER BY 
                expiry_date IS NULL,
                expiry_date ASC,
                box_id ASC
            LIMIT " . intval($qtyNeeded)
        );

        $boxes->bind_param("i", $productId);
        $boxes->execute();
        $boxResult = $boxes->get_result();

        if ($boxResult->num_rows == 0) {
            throw new Exception("No available boxes for product ID: $productId");
        }

        while ($box = $boxResult->fetch_assoc()) {

            $insert = $conn->prepare("
                INSERT INTO tbl_trip_picklist
                (trip_id, job_order_id, box_id, product_id, pallet_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            $insert->bind_param(
                "iiiii",
                $tripId,
                $jobOrderId,
                $box['box_id'],
                $productId,
                $box['pallet_id']
            );

            $insert->execute();

            // reserve box
            $update = $conn->prepare("
                UPDATE tbl_stock_boxes
                SET status = 'reserved'
                WHERE box_id = ?
            ");

            $update->bind_param("i", $box['box_id']);
            $update->execute();
        }
    }
}
