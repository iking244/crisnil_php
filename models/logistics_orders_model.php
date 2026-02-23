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
    $sql = "
        SELECT 
            p.product_id,
            p.product_name,
            u.unit_name AS unit
        FROM tbl_products p
        LEFT JOIN tbl_units u
            ON p.unit_id = u.unit_id
        WHERE p.is_active = 1
        ORDER BY p.product_name
    ";
    return $conn->query($sql);
}


/* =========================================================
   JOB ORDER CREATION & UPDATING
========================================================= */

function createLogisticsOrder($conn, $warehouse_id, $client_id, $product_ids, $quantities)
{
    mysqli_begin_transaction($conn);

    try {

        /* =========================
           INIT FINANCIAL LAYER
        ========================== */
        require_once "../financial/FinancialRepository.php";
        require_once "../financial/FinancialService.php";

        $financialRepo = new FinancialRepository($conn);
        $financialService = new FinancialService($financialRepo);

        /* =========================
           GET WAREHOUSE
        ========================== */
        $warehouseQuery = mysqli_query($conn, "
            SELECT warehouse_name, latitude, longitude
            FROM tbl_warehouses 
            WHERE warehouse_id = $warehouse_id
        ");

        $warehouseData = mysqli_fetch_assoc($warehouseQuery);

        if (!$warehouseData) {
            throw new Exception("Warehouse not found");
        }

        /* =========================
           GET CLIENT
        ========================== */
        $clientQuery = mysqli_query($conn, "
            SELECT client_name, latitude, longitude
            FROM tbl_clients 
            WHERE client_id = $client_id
        ");

        $clientData = mysqli_fetch_assoc($clientQuery);

        if (!$clientData) {
            throw new Exception("Client not found");
        }

        /* =========================
           INSERT JOB ORDER
        ========================== */
        $stmt = $conn->prepare("
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
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->bind_param(
            "sddsdd",
            $warehouseData['warehouse_name'],
            $warehouseData['latitude'],
            $warehouseData['longitude'],
            $clientData['client_name'],
            $clientData['latitude'],
            $clientData['longitude']
        );

        $stmt->execute();
        $job_id = $stmt->insert_id;
        $stmt->close();

        /* =========================
           INSERT ITEMS VIA FINANCIAL SERVICE
        ========================== */
        foreach ($product_ids as $index => $product_id) {

            $product_id = (int)$product_id;
            $qty = (int)$quantities[$index];

            if ($product_id > 0 && $qty > 0) {
                $financialService->addItemWithoutRecompute($job_id, $product_id, $qty);
            }
        }

        /* =========================
           RECOMPUTE TOTALS ONCE
        ========================== */
        $financialService->recomputeTotals($job_id);

        /* =========================
           RESERVE STOCK
        ========================== */
        reserveStock(
            $conn,
            $job_id,
            $warehouse_id,
            $product_ids,
            $quantities
        );

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
                SELECT SUM(r.quantity)
                FROM tbl_stock_reservations r
                WHERE r.product_id = $product_id
                AND r.warehouse_id = $warehouse_id
            ), 0) AS available_stock
        FROM tbl_warehouse_stock ws
        WHERE ws.product_id = $product_id
        AND ws.warehouse_id = $warehouse_id
        AND ws.expiration_date >= CURDATE()
    ");

    $row = mysqli_fetch_assoc($query);
    return $row['available_stock'] ?? 0;
}

/* =========================
   STOCK RESERVATION LOGIC
========================= */

function reserveStock($conn, $job_id, $warehouse_id, $product_ids, $quantities)
{
    foreach ($product_ids as $index => $product_id) {
        $product_id = (int)$product_id;
        $qty = (int)$quantities[$index];

        if ($product_id > 0 && $qty > 0) {

            // Check available stock
            $available = getAvailableStock($conn, $warehouse_id, $product_id);

            if ($qty > $available) {
                throw new Exception(
                    "Insufficient stock for product ID: " . $product_id
                );
            }
            // Insert reservation
            $stmt = $conn->prepare("
                INSERT INTO tbl_stock_reservations
                (job_order_id, product_id, warehouse_id, quantity)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "iiii",
                $job_id,
                $product_id,
                $warehouse_id,
                $qty
            );
            $stmt->execute();
        }
    }
}

function releaseStockReservation($conn, $job_id)
{
    $stmt = $conn->prepare("
        DELETE FROM tbl_stock_reservations
        WHERE job_order_id = ?
    ");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
}

function hasStockReservation($conn, $job_id)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM tbl_stock_reservations
        WHERE job_order_id = ?
    ");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total'] > 0;
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
            u.unit_name AS unit,
            joi.quantity
        FROM tbl_job_orders jo
        INNER JOIN tbl_job_order_items joi
            ON jo.id = joi.job_order_id
        INNER JOIN tbl_products p
            ON joi.product_id = p.product_id
        INNER JOIN tbl_units u
            ON p.unit_id = u.unit_id
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
    // 1. Check for blocked job orders
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

    // 2. Start transaction
    $conn->begin_transaction();

    try {

        // 3. Get warehouse ID (must exist)
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

        if (!$warehouseData) {
            throw new Exception("Warehouse not found for this trip");
        }

        $warehouse_id = (int)$warehouseData['warehouse_id'];

        // 4. Get all required items
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

        // Prepare reusable statements
        $batchQuery = $conn->prepare("
            SELECT stock_id, quantity
            FROM tbl_warehouse_stock
            WHERE product_id = ?
            AND warehouse_id = ?
            AND quantity > 0
            AND expiration_date >= CURDATE()
            ORDER BY expiration_date ASC
        ");

        $updateStock = $conn->prepare("
            UPDATE tbl_warehouse_stock
            SET quantity = GREATEST(quantity - ?, 0)
            WHERE stock_id = ?
        ");

        // 5. Deduct stock using FEFO
        while ($row = $resultItems->fetch_assoc()) {

            $product_id = (int)$row['product_id'];
            $qty_needed = (int)$row['quantity'];

            $batchQuery->bind_param("ii", $product_id, $warehouse_id);
            $batchQuery->execute();
            $batches = $batchQuery->get_result();

            while ($qty_needed > 0 && $batch = $batches->fetch_assoc()) {
                $stock_id = (int)$batch['stock_id'];
                $batch_qty = (int)$batch['quantity'];

                $deduct = min($batch_qty, $qty_needed);

                $updateStock->bind_param("ii", $deduct, $stock_id);
                $updateStock->execute();

                $qty_needed -= $deduct;
            }

            if ($qty_needed > 0) {
                throw new Exception("Insufficient stock during FEFO deduction");
            }
        }

        // 6. Delete reservations AFTER successful deduction
        $release = $conn->prepare("
            DELETE FROM tbl_stock_reservations
            WHERE job_order_id IN (
                SELECT id
                FROM tbl_job_orders
                WHERE trip_id = ?
            )
        ");
        $release->bind_param("i", $trip_id);
        $release->execute();

        // 7. Update trip status
        $stmt = $conn->prepare("
            UPDATE tbl_trips
            SET status = 'ready_to_depart'
            WHERE trip_id = ?
            AND status NOT IN ('completed', 'cancelled')
        ");
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();

        // 8. Commit
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
    $job_id = intval($job_id);

    $query = "
    SELECT 
        joi.job_item_id,
        joi.product_id,
        p.product_name,
        joi.quantity,
        ws.quantity AS stock_qty
    FROM tbl_job_order_items joi
    LEFT JOIN tbl_products p 
        ON joi.product_id = p.product_id
    LEFT JOIN tbl_warehouse_stock ws
        ON joi.product_id = ws.product_id
        AND ws.warehouse_id = 1
    WHERE joi.job_order_id = $job_id
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

/* =========================
   OVERVIEW STATS
========================= */

function getLogisticsOverviewStats($conn)
{
    $stats = [];

    // Total
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_job_orders");
    $stats['total'] = mysqli_fetch_assoc($result)['total'];

    // Pending
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_job_orders WHERE status='pending'");
    $stats['pending'] = mysqli_fetch_assoc($result)['total'];

    // In Transit
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_job_orders WHERE status='in_transit'");
    $stats['in_transit'] = mysqli_fetch_assoc($result)['total'];

    // Completed Today
    $result = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM tbl_job_orders 
        WHERE status='completed'
        AND DATE(updated_at)=CURDATE()
    ");
    $stats['completed_today'] = mysqli_fetch_assoc($result)['total'];

    return $stats;
}


/* =========================
   RECENT ORDERS
========================= */

function getRecentLogisticsOrders($conn, $limit = 8)
{
    return mysqli_query($conn, "
        SELECT id, origin, destination, status, created_at
        FROM tbl_job_orders
        ORDER BY created_at DESC
        LIMIT $limit
    ");
}


/* =========================
   STATUS BREAKDOWN
========================= */

function getLogisticsStatusBreakdown($conn)
{
    return mysqli_query($conn, "
        SELECT status, COUNT(*) as total
        FROM tbl_job_orders
        GROUP BY status
    ");
}

/* =========================
   OPERATIONAL ALERTS
========================= */

function getLogisticsOperationalAlerts($conn)
{
    $alerts = [];

    $statuses = ['pending', 'blocked', 'cancelled', 'in_transit'];

    foreach ($statuses as $status) {
        $result = mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM tbl_job_orders 
            WHERE status = '$status'
        ");
        $alerts[$status] = mysqli_fetch_assoc($result)['total'];
    }

    return $alerts;
}