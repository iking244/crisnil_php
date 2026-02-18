<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once "../../config/database_conn.php";
header('Content-Type: application/json');

function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) *
         cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

try {

    // START TRANSACTION
    $databaseconn->begin_transaction();

    // 1. Get pending jobs
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

    // 2. Get available trucks
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

// 3. Cluster jobs (radius-based with max size)
$radius = 5; // km
$maxOrdersPerCluster = 5; // editable limit
$clusters = [];

while (!empty($jobs)) {
    $base = array_shift($jobs);
    $cluster = [$base];

    foreach ($jobs as $key => $job) {

        // stop if cluster reached limit
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


    // Starting point (warehouse or default)
    $startLat = 14.6091;
    $startLng = 121.0223;

    // 4. Assign clusters to trucks with sequencing
    $assigned = 0;

    foreach ($clusters as $index => $cluster) {

        if (!isset($trucks[$index])) {
            break;
        }

        $plate = $trucks[$index]['PLATE_NUM'];

        // create trip
        $stmt = $databaseconn->prepare("
            INSERT INTO tbl_trips
            (truck_plate_number, status, created_at,warehouse_id)
            VALUES (?, 'pending_loading', NOW(),1)
        ");
        $stmt->bind_param("s", $plate);

        if (!$stmt->execute()) {
            throw new Exception("Failed to create trip");
        }

        $tripId = $stmt->insert_id;

        // ---------- DELIVERY SEQUENCING ----------
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

            // assign job with sequence
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

            // move to next location
            $currentLat = $nearestJob['destination_lat'];
            $currentLng = $nearestJob['destination_lng'];

            unset($jobsForRouting[$nearestIndex]);
            $sequence++;
            $assigned++;
        }
    }

    // COMMIT if everything succeeded
    $databaseconn->commit();

    echo json_encode([
        "message" => "Auto assignment completed",
        "jobs_assigned" => $assigned,
        "clusters_created" => count($clusters)
    ]);

} catch (Exception $e) {

    // ROLLBACK on any failure
    $databaseconn->rollback();

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
