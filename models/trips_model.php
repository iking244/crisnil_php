<?php
function createTrip($conn, $driver_id, $truck_plate, $warehouse_id)
{

    $sql = "INSERT INTO tbl_trips
            (truck_plate_number, warehouse_id, status)
            VALUES
            ('$truck_plate', '$warehouse_id', 'pending_loading')";

    if ($conn->query($sql)) {
        return $conn->insert_id;
    }

    return false;
}


function getActiveDrivers($conn)
{
    $sql = "SELECT 
    u.USER_ID,
    u.FIRST_NAME,
    u.LAST_NAME,
    CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) AS USER_NAME
    FROM crisnil_users u
        LEFT JOIN tbl_trips t
                ON u.USER_ID = t.driver_id
                AND t.status IN ('pending_loading', 'in_transit','loading','ready_to_depart')
                WHERE u.USER_BIO = 'RIDER'
                AND t.trip_id IS NULL

    ";
    return $conn->query($sql);
}

function getActiveWarehouses($conn)
{
    $sql = "SELECT * FROM tbl_warehouses WHERE is_active = 1";
    return $conn->query($sql);
}

function getActiveTrips($conn)
{
    $sql = "
        SELECT 
            trip_id,
            truck_plate_number,
            status,
            departure_time
        FROM tbl_trips
        ORDER BY trip_id ASC
    ";

    return $conn->query($sql);
}

function getInTransitTrips($conn)
{
    $sql = "
        SELECT 
            trip_id,
            truck_plate_number,
            status,
            departure_time
        FROM tbl_trips
        WHERE status = 'in_transit'
        ORDER BY trip_id ASC
    ";

    return $conn->query($sql);
}

function getAllTripsWithStats($conn)
{
    $sql = "
        SELECT 
            t.trip_id,
            t.truck_plate_number,
            t.status,
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as driver_name,
            COUNT(j.id) as job_count
        FROM tbl_trips t
        LEFT JOIN crisnil_users u ON t.driver_id = u.USER_ID
        LEFT JOIN tbl_job_orders j ON j.trip_id = t.trip_id
        GROUP BY t.trip_id
        ORDER BY t.trip_id ASC
    ";

    return $conn->query($sql);
}

function getTripsPaginated($conn, $limit, $offset)
{
    $sql = "
        SELECT 
            t.trip_id,
            t.truck_plate_number,
            t.status,
            CONCAT(u.FIRST_NAME, ' ', u.LAST_NAME) as driver_name,
            COUNT(j.id) as job_count
        FROM tbl_trips t
        LEFT JOIN crisnil_users u ON t.driver_id = u.USER_ID
        LEFT JOIN tbl_job_orders j ON j.trip_id = t.trip_id
        GROUP BY t.trip_id
        ORDER BY t.trip_id DESC
        LIMIT $limit OFFSET $offset
    ";

    return $conn->query($sql);
}

function countAllTrips($conn)
{
    $sql = "SELECT COUNT(*) as total FROM tbl_trips";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}



function getTripById($conn, $trip_id)
{
    $sql = "SELECT * FROM tbl_trips WHERE trip_id = $trip_id";
    return $conn->query($sql)->fetch_assoc();
}

function updateTripInfo($conn, $trip_id, $driver_id, $truck_plate)
{
    $sql = "
        UPDATE tbl_trips
        SET driver_id = '$driver_id',
            truck_plate_number = '$truck_plate'
        WHERE trip_id = $trip_id
    ";
    return $conn->query($sql);
}

function getAvailableTrucks($conn)
{
    $sql = "
        SELECT f.PK_FLEET, f.PLATE_NUM, f.MODEL
        FROM tbl_fleetlist f
        WHERE f.FLEET_STATUS = 'ACTIVE'
        AND f.PLATE_NUM NOT IN (
            SELECT t.truck_plate_number
            FROM tbl_trips t
            WHERE t.status IN ('pending_loading','loading','ready_to_depart','in_transit')
        )
        ORDER BY f.PLATE_NUM ASC
    ";

    return $conn->query($sql);
}

function startTrip($conn, $trip_id)
{
    $conn->begin_transaction();

    try {
        // Update trip
        $stmt = $conn->prepare("
            UPDATE tbl_trips
            SET status = 'in_transit'
            WHERE trip_id = ?
            AND status = 'ready_to_depart'
        ");
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();

        // Update only assigned jobs
        $stmt = $conn->prepare("
            UPDATE tbl_job_orders
            SET status = 'in_transit'
            WHERE trip_id = ?
            AND status = 'assigned'
        ");
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();

        $conn->commit();

        return [
            "success" => true,
            "message" => "Trip started"
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return [
            "success" => false,
            "message" => "Error starting trip"
        ];
    }
}

function insertTrackingLog($conn, $trip_id, $driver_id, $lat, $lng)
{
    $conn->begin_transaction();

    try {
        // 1. Insert into tracking logs
        $stmt1 = $conn->prepare("
            INSERT INTO tbl_tracking_logs 
            (trip_id, driver_id, latitude, longitude, recorded_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt1->bind_param("iidd", $trip_id, $driver_id, $lat, $lng);
        $stmt1->execute();

        // 2. Update current location in trips table
        $stmt2 = $conn->prepare("
            UPDATE tbl_trips
            SET current_latitude = ?,
                current_longitude = ?,
                last_location_update = NOW()
            WHERE trip_id = ?
        ");
        $stmt2->bind_param("ddi", $lat, $lng, $trip_id);
        $stmt2->execute();

        // 3. Cleanup old logs (keep last 300)
        $cleanup = $conn->prepare("
            DELETE FROM tbl_tracking_logs
            WHERE trip_id = ?
            AND tracking_log_id NOT IN (
                SELECT tracking_log_id FROM (
                    SELECT tracking_log_id
                    FROM tbl_tracking_logs
                    WHERE trip_id = ?
                    ORDER BY tracking_log_id DESC
                    LIMIT 300
                ) as temp
            )
        ");
        $cleanup->bind_param("ii", $trip_id, $trip_id);
        $cleanup->execute();

        $conn->commit();

        return [
            "success" => true,
            "message" => "Location updated"
        ];
    } catch (Exception $e) {
        $conn->rollback();

        return [
            "success" => false,
            "message" => "Tracking update failed"
        ];
    }
}



function completeTrip($conn, $trip_id)
{
    // Check if any job is not completed
    $check = $conn->prepare("
        SELECT COUNT(*) as total
        FROM tbl_job_orders
        WHERE trip_id = ?
        AND status != 'completed'
    ");
    $check->bind_param("i", $trip_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        return [
            "success" => false,
            "message" => "Not all jobs are completed"
        ];
    }

    // Update trip status
    $stmt = $conn->prepare("
        UPDATE tbl_trips
        SET status = 'completed'
        WHERE trip_id = ?
        AND status = 'in_transit'
    ");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return [
            "success" => true,
            "message" => "Trip completed"
        ];
    } else {
        return [
            "success" => false,
            "message" => "Trip cannot be completed"
        ];
    }
}
