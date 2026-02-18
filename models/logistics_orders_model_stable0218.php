<?php

/* =========================================================
   JOB ORDER LISTING & SEARCH
========================================================= */

function getAllJobOrders($conn)
{
    $sql = "SELECT id, origin, destination, status, created_at, eta 
            FROM tbl_job_orders 
            ORDER BY created_at DESC";
    return $conn->query($sql);
}

function searchJobOrders($conn, $term)
{
    $term = "%$term%";
    $stmt = $conn->prepare("SELECT * FROM tbl_job_orders 
                           WHERE origin LIKE ? OR destination LIKE ? 
                           ORDER BY created_at DESC");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    return $stmt->get_result();
}

function countAllJobOrders($conn)
{
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_job_orders");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getJobOrdersPaginated($conn, $limit, $offset)
{
    $sql = "
        SELECT 
            id,
            origin,
            destination,
            status,
            created_at,
            eta
        FROM tbl_job_orders
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    return $conn->query($sql);
}

function getAllLogisticsOrders($conn)
{
    $query = "SELECT * FROM tbl_job_orders ORDER BY id DESC";
    $result = mysqli_query($conn, $query);

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

    return $orders;
}


/* =========================================================
   MASTER DATA (WAREHOUSES, CLIENTS, PRODUCTS)
========================================================= */

function getActiveWarehouses($conn)
{
    $sql = "SELECT * FROM tbl_warehouses WHERE is_active = 1 ORDER BY warehouse_name";
    return $conn->query($sql);
}

function getActiveClients($conn)
{
    $sql = "SELECT * FROM tbl_clients WHERE is_active = 1 ORDER BY client_name";
    return $conn->query($sql);
}

function getActiveProducts($conn)
{
    $sql = "SELECT * FROM tbl_products WHERE is_active = 1 ORDER BY product_name";
    return $conn->query($sql);
}


/* =========================================================
   JOB ORDER CREATION & UPDATING
========================================================= */

function createLogisticsOrder($conn, $warehouse_id, $client_id, $product_ids, $quantities)
{
    mysqli_begin_transaction($conn);

    try {
        // Get warehouse data
        $warehouseQuery = mysqli_query($conn, "
            SELECT warehouse_name, latitude, longitude
            FROM tbl_warehouses 
            WHERE warehouse_id = $warehouse_id
        ");
        $warehouseData = mysqli_fetch_assoc($warehouseQuery);

        if (!$warehouseData) {
            throw new Exception("Warehouse not found");
        }

        $warehouse_name = $warehouseData['warehouse_name'];
        $origin_lat = $warehouseData['latitude'];
        $origin_lng = $warehouseData['longitude'];

        // Get client data
        $clientQuery = mysqli_query($conn, "
            SELECT client_name, latitude, longitude
            FROM tbl_clients 
            WHERE client_id = $client_id
        ");
        $clientData = mysqli_fetch_assoc($clientQuery);

        if (!$clientData) {
            throw new Exception("Client not found");
        }

        $client_name = $clientData['client_name'];
        $destination_lat = $clientData['latitude'];
        $destination_lng = $clientData['longitude'];

        // Insert job order
        $insertOrder = mysqli_query($conn, "
            INSERT INTO tbl_job_orders (
                origin,
                origin_lat,
                origin_lng,
                destination,
                destination_lat,
                destination_lng,
                status,
                created_at
            )
            VALUES (
                '$warehouse_name',
                '$origin_lat',
                '$origin_lng',
                '$client_name',
                '$destination_lat',
                '$destination_lng',
                'pending',
                NOW()
            )
        ");

        if (!$insertOrder) {
            throw new Exception("Failed to insert job order");
        }

        $job_id = mysqli_insert_id($conn);

        // Insert items
        foreach ($product_ids as $index => $product_id) {
            $product_id = (int)$product_id;
            $qty = (int)$quantities[$index];

            if ($product_id > 0 && $qty > 0) {
                $insertItem = mysqli_query($conn, "
                    INSERT INTO tbl_job_order_items 
                    (job_order_id, product_id, quantity)
                    VALUES ($job_id, $product_id, $qty)
                ");

                if (!$insertItem) {
                    throw new Exception("Failed to insert job order item");
                }
            }
        }

        mysqli_commit($conn);
        return $job_id;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

function updateLogisticsOrder($conn, $job_id, $status, $eta)
{
    $eta_sql = $eta ? "'$eta'" : "NULL";

    $currentQuery = mysqli_query($conn, "
        SELECT status 
        FROM tbl_job_orders 
        WHERE id = $job_id
    ");
    $current = mysqli_fetch_assoc($currentQuery);
    $old_status = $current['status'];

    mysqli_query($conn, "
        UPDATE tbl_job_orders
        SET status = '$status',
            eta = $eta_sql
        WHERE id = $job_id
    ");

    if ($old_status !== 'in_transit' && $status === 'in_transit') {
        deductWarehouseStock($conn, $job_id);
    }
}

function replaceLogisticsOrderItems($conn, $job_id, $product_ids, $quantities)
{
    mysqli_query($conn, "
        DELETE FROM tbl_job_order_items
        WHERE job_order_id = $job_id
    ");

    for ($i = 0; $i < count($product_ids); $i++) {
        $pid = (int)$product_ids[$i];
        $qty = (int)$quantities[$i];

        if ($pid > 0 && $qty > 0) {
            mysqli_query($conn, "
                INSERT INTO tbl_job_order_items
                (job_order_id, product_id, quantity)
                VALUES ($job_id, $pid, $qty)
            ");
        }
    }
}


/* =========================================================
   STOCK CHECKING & AVAILABILITY
========================================================= */

function checkStockAvailability($conn, $warehouse_id, $product_ids, $quantities)
{
    foreach ($product_ids as $index => $product_id) {
        $product_id = (int)$product_id;
        $qty_needed = (int)$quantities[$index];

        $available = getAvailableStock($conn, $warehouse_id, $product_id);

        if ($qty_needed > $available) {
            return false;
        }
    }

    return true;
}

function getAvailableStock($conn, $warehouse_id, $product_id)
{
    $query = mysqli_query($conn, "
        SELECT 
            IFNULL(SUM(ws.quantity), 0)
            - IFNULL((
                SELECT SUM(joi.quantity)
                FROM tbl_job_order_items joi
                INNER JOIN tbl_job_orders jo
                    ON joi.job_order_id = jo.id
                WHERE joi.product_id = $product_id
                AND jo.status IN ('pending', 'assigned')
            ), 0) AS available_stock
        FROM tbl_warehouse_stock ws
        WHERE ws.product_id = $product_id
        AND ws.warehouse_id = $warehouse_id
        AND ws.expiration_date >= CURDATE()
    ");

    $row = mysqli_fetch_assoc($query);
    return $row['available_stock'] ?? 0;
}


/* =========================================================
   DRIVER / TRIP API FUNCTIONS
========================================================= */

function getDriverJobOrders($conn, $driver_id)
{
    $stmt = $conn->prepare("
        SELECT 
            jo.id AS job_id,
            jo.trip_id,
            jo.origin,
            jo.destination,
            jo.destination_lng,
            jo.destination_lat,
            jo.status,
            t.status AS trip_status
        FROM tbl_job_orders jo
        INNER JOIN tbl_trips t 
            ON jo.trip_id = t.trip_id
        WHERE 
            t.driver_id = ?
            AND t.status NOT IN ('completed', 'cancelled')
        ORDER BY t.trip_id DESC, jo.id ASC
    ");

    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getTripJobItems($conn, $trip_id)
{
    $stmt = $conn->prepare("
        SELECT 
            jo.id AS job_id,
            joi.job_item_id,
            joi.product_id,
            p.product_code,
            p.product_name,
            p.unit,
            joi.quantity
        FROM tbl_job_orders jo
        INNER JOIN tbl_job_order_items joi
            ON jo.id = joi.job_order_id
        INNER JOIN tbl_products p
            ON joi.product_id = p.product_id
        WHERE jo.trip_id = ?
        ORDER BY jo.id ASC
    ");

    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getJobItems($conn, $job_id)
{
    $stmt = $conn->prepare("
        SELECT 
            jo.id AS job_id,
            joi.job_item_id,
            joi.product_id,
            p.product_code,
            p.product_name,
            p.unit,
            joi.quantity
        FROM tbl_job_orders jo
        INNER JOIN tbl_job_order_items joi
            ON jo.id = joi.job_order_id
        INNER JOIN tbl_products p
            ON joi.product_id = p.product_id
        WHERE jo.id = ?
        ORDER BY joi.job_item_id ASC
    ");

    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    return $stmt->get_result();
}


/* =========================================================
   DRIVER ACTIONS
========================================================= */

function blockJobOrder($conn, $job_id, $reason = '')
{
    $stmt = $conn->prepare("
        UPDATE tbl_job_orders
        SET status = 'blocked'
        WHERE id = ?
    ");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        INSERT INTO tbl_job_order_logs (job_id, action, notes)
        VALUES (?, 'blocked', ?)
    ");
    $stmt2->bind_param("is", $job_id, $reason);
    $stmt2->execute();

    return [
        "success" => true,
        "message" => "Job order blocked"
    ];
}

function completeJobOrder($conn, $job_id)
{
    $stmt = $conn->prepare("
        UPDATE tbl_job_orders
        SET status = 'completed'
        WHERE id = ?
        AND status = 'in_transit'
    ");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $log = $conn->prepare("
            INSERT INTO tbl_job_order_logs (job_id, action, notes)
            VALUES (?, 'completed', 'Completed by driver')
        ");
        $log->bind_param("i", $job_id);
        $log->execute();

        return [
            "success" => true,
            "message" => "Job completed"
        ];
    } else {
        return [
            "success" => false,
            "message" => "Job cannot be completed"
        ];
    }
}


/* =========================================================
   TRIP LOADING (FEFO STOCK DEDUCTION)
========================================================= */

function confirmTripLoaded($conn, $trip_id)
{
    $check = $conn->prepare("
        SELECT COUNT(*) as total
        FROM tbl_job_orders
        WHERE trip_id = ?
        AND status = 'blocked'
    ");
    $check->bind_param("i", $trip_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();

    if ($result['total'] > 0) {
        return [
            "success" => false,
            "message" => "Trip has blocked job orders"
        ];
    }

    $conn->begin_transaction();

    try {
        $items = $conn->prepare("
            SELECT joi.product_id, joi.quantity
            FROM tbl_job_order_items joi
            INNER JOIN tbl_job_orders jo
                ON joi.job_order_id = jo.id
            WHERE jo.trip_id = ?
            AND jo.status = 'assigned'
        ");
        $items->bind_param("i", $trip_id);
        $items->execute();
        $resultItems = $items->get_result();

        $warehouseQuery = $conn->prepare("
            SELECT w.warehouse_id
            FROM tbl_job_orders jo
            INNER JOIN tbl_warehouses w
                ON jo.origin = w.warehouse_name
            WHERE jo.trip_id = ?
            LIMIT 1
        ");
        $warehouseQuery->bind_param("i", $trip_id);
        $warehouseQuery->execute();
        $warehouseData = $warehouseQuery->get_result()->fetch_assoc();

        $warehouse_id = $warehouseData['warehouse_id'] ?? 0;

        while ($row = $resultItems->fetch_assoc()) {

            $product_id = (int)$row['product_id'];
            $qty_needed = (int)$row['quantity'];

            $batchQuery = $conn->prepare("
                SELECT stock_id, quantity
                FROM tbl_warehouse_stock
                WHERE product_id = ?
                AND warehouse_id = ?
                AND quantity > 0
                AND expiration_date >= CURDATE()
                ORDER BY expiration_date ASC
            ");
            $batchQuery->bind_param("ii", $product_id, $warehouse_id);
            $batchQuery->execute();
            $batches = $batchQuery->get_result();

            while ($qty_needed > 0 && $batch = $batches->fetch_assoc()) {
                $stock_id = (int)$batch['stock_id'];
                $batch_qty = (int)$batch['quantity'];

                $deduct = ($batch_qty >= $qty_needed)
                    ? $qty_needed
                    : $batch_qty;

                $update = $conn->prepare("
                    UPDATE tbl_warehouse_stock
                    SET quantity = GREATEST(quantity - ?, 0)
                    WHERE stock_id = ?
                ");
                $update->bind_param("ii", $deduct, $stock_id);
                $update->execute();

                $qty_needed -= $deduct;
            }

            if ($qty_needed > 0) {
                throw new Exception("Insufficient stock during FEFO deduction");
            }
        }

        $stmt = $conn->prepare("
            UPDATE tbl_trips
            SET status = 'ready_to_depart'
            WHERE trip_id = ?
            AND status NOT IN ('completed', 'cancelled')
        ");
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();

        $conn->commit();

        return [
            "success" => true,
            "message" => "Trip is ready to depart"
        ];
    } catch (Exception $e) {
        $conn->rollback();
        return [
            "success" => false,
            "message" => $e->getMessage()
        ];
    }
}


/* =========================================================
   LEGACY / TEMPORARY FUNCTIONS (TO BE REFACTORED)
========================================================= */

// temporary warehouse_id only
function getLogisticsOrderItems($conn, $job_id)
{
    $query = "
    SELECT 
        joi.job_item_id,
        joi.product_id,
        p.product_name,
        p.unit,
        joi.quantity,
        ws.quantity AS stock_qty
    FROM tbl_job_order_items joi
    LEFT JOIN tbl_products p 
        ON joi.product_id = p.product_id
    LEFT JOIN tbl_warehouse_stock ws
        ON joi.product_id = ws.product_id
    WHERE joi.job_order_id = $job_id
    AND ws.warehouse_id = 1
    ";

    $result = mysqli_query($conn, $query);

    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    return $items;
}

function deductWarehouseStock($conn, $job_id)
{
    $orderQuery = mysqli_query($conn, "
        SELECT origin
        FROM tbl_job_orders
        WHERE id = $job_id
    ");
    $order = mysqli_fetch_assoc($orderQuery);
    $origin = $order['origin'];

    $warehouseQuery = mysqli_query($conn, "
        SELECT warehouse_id
        FROM tbl_warehouses
        WHERE warehouse_name = '$origin'
    ");
    $warehouse = mysqli_fetch_assoc($warehouseQuery);
    $warehouse_id = $warehouse['warehouse_id'];

    $itemsQuery = mysqli_query($conn, "
        SELECT product_id, quantity
        FROM tbl_job_order_items
        WHERE job_order_id = $job_id
    ");

    while ($item = mysqli_fetch_assoc($itemsQuery)) {
        $product_id = (int)$item['product_id'];
        $qty = (int)$item['quantity'];

        mysqli_query($conn, "
            UPDATE tbl_warehouse_stock
            SET quantity = quantity - $qty
            WHERE product_id = $product_id
            AND warehouse_id = $warehouse_id
        ");
    }
}
