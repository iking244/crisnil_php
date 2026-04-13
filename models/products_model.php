<?php

/* =========================
   GET ALL WAREHOUSES
========================= */
function getAllWarehouses($conn)
{
    $query = "
        SELECT warehouse_id, warehouse_name
        FROM tbl_warehouses
        ORDER BY warehouse_name ASC
    ";

    return mysqli_query($conn, $query);
}


/* =========================
   GET ALL PRODUCTS
========================= */
function getAllProducts($conn)
{
    $query = "
        SELECT 
            p.product_id,
            p.product_code,
            p.product_name,
            u.unit_name AS unit,
            IFNULL(SUM(ws.quantity), 0) AS quantity,
            IFNULL(SUM(ws.quantity) * p.weight_per_unit, 0) AS weight,
            IFNULL(FLOOR(SUM(ws.quantity) / p.units_per_pallet), 0) AS pallets
        FROM tbl_products p
        LEFT JOIN tbl_units u
            ON p.unit_id = u.unit_id
        LEFT JOIN tbl_warehouse_stock ws 
            ON p.product_id = ws.product_id
        GROUP BY p.product_id
        ORDER BY p.product_name ASC
    ";

    return mysqli_query($conn, $query);
}

function getAllProductsName($conn)
{
    $sql = "SELECT product_id, product_name FROM tbl_products ORDER BY product_name ASC";
    return mysqli_query($conn, $sql);
}



/* =========================
   COUNT PRODUCTS
========================= */
function countAllProducts($conn)
{
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_products");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}


/* =========================
   PAGINATED PRODUCTS
========================= */
function getProductsPaginated_0218($conn, $warehouse_id, $limit, $offset)
{
    $query = "
        SELECT 
            p.product_id,
            p.product_code,
            p.product_name,
            p.unit_id,
            p.weight_per_unit,
            p.units_per_pallet,
            u.unit_name AS unit,
            IFNULL(SUM(ws.quantity), 0) AS quantity,
            IFNULL(SUM(ws.quantity) * p.weight_per_unit, 0) AS weight,
            IFNULL(FLOOR(SUM(ws.quantity) / p.units_per_pallet), 0) AS pallets
        FROM tbl_products p
        LEFT JOIN tbl_units u
            ON p.unit_id = u.unit_id
        LEFT JOIN tbl_warehouse_stock ws 
            ON p.product_id = ws.product_id
            AND ws.warehouse_id = $warehouse_id
        GROUP BY p.product_id
        ORDER BY p.product_name ASC
        LIMIT $limit OFFSET $offset
    ";

    return mysqli_query($conn, $query);
}

function getProductsPaginated($conn, $warehouse_id, $limit, $offset)
{

    // If warehouse_id = 0 → show all warehouses
    if ($warehouse_id == 0) {

        $query = "
    SELECT 
        p.product_id,
        p.product_code,
        p.product_name,

        COUNT(sb.box_id) AS quantity,
        COALESCE(SUM(sb.box_weight), 0) AS weight

    FROM tbl_products p

    LEFT JOIN tbl_stock_boxes sb
        ON p.product_id = sb.product_id
        AND sb.status = 'available'
        AND sb.condition_status = 'good'   

    GROUP BY 
        p.product_id,
        p.product_code,
        p.product_name

    ORDER BY p.product_name ASC
    LIMIT $limit OFFSET $offset
        ";
    } else {

        $query = "
    SELECT 
        p.product_id,
        p.product_code,
        p.product_name,

        COUNT(sb.box_id) AS quantity,
        COALESCE(SUM(sb.box_weight), 0) AS weight

    FROM tbl_products p

    LEFT JOIN tbl_stock_boxes sb
        ON p.product_id = sb.product_id
        AND sb.status = 'available'
        AND sb.condition_status = 'good'       
        AND ($warehouse_id = 0 OR sb.warehouse_id = $warehouse_id)

    GROUP BY 
        p.product_id,
        p.product_code,
        p.product_name

    ORDER BY p.product_name ASC
    LIMIT $limit OFFSET $offset
";
    }

    return mysqli_query($conn, $query);
}



/* =========================
   CREATE PRODUCT
========================= */
function createProduct($conn, $warehouse_id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet, $production_date, $expiration_date)
{
    mysqli_begin_transaction($conn);

    try {
        // Check if product code already exists
        $check = mysqli_query($conn, "
            SELECT product_id 
            FROM tbl_products 
            WHERE product_code = '$code'
            LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            throw new Exception("duplicate_code");
        }

        // Insert product
        $productQuery = "
            INSERT INTO tbl_products 
            (product_code, product_name, unit_id, weight_per_unit, units_per_pallet)
            VALUES 
            ('$code', '$name', $unit_id, $weight_per_unit, $units_per_pallet)
        ";

        if (!mysqli_query($conn, $productQuery)) {
            throw new Exception(mysqli_error($conn));
        }

        $product_id = mysqli_insert_id($conn);

        // Insert initial stock batch
        $stockQuery = "
            INSERT INTO tbl_warehouse_stock
            (warehouse_id, product_id, quantity, production_date, expiration_date)
            VALUES
            ($warehouse_id, $product_id, $qty, '$production_date', '$expiration_date')
        ";

        if (!mysqli_query($conn, $stockQuery)) {
            throw new Exception(mysqli_error($conn));
        }

        mysqli_commit($conn);
        return ["success" => true];
    } catch (Exception $e) {
        mysqli_rollback($conn);

        if ($e->getMessage() === "duplicate_code") {
            return ["success" => false, "error" => "Product code already exists."];
        }

        return ["success" => false, "error" => $e->getMessage()];
    }
}

/* =========================
   UPDATE PRODUCT
========================= */
function updateProduct($conn, $warehouse_id, $id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet)
{

    mysqli_begin_transaction($conn);

    try {
        // Escape strings
        $code = mysqli_real_escape_string($conn, $code);
        $name = mysqli_real_escape_string($conn, $name);

        // Update product
        $query1 = "
            UPDATE tbl_products
            SET 
                product_code = '$code',
                product_name = '$name',
                unit_id = $unit_id,
                weight_per_unit = $weight_per_unit,
                units_per_pallet = $units_per_pallet
            WHERE product_id = $id
        ";

        if (!mysqli_query($conn, $query1)) {
            throw new Exception(mysqli_error($conn));
        }

        // Update stock
        $query2 = "
            UPDATE tbl_warehouse_stock
            SET quantity = $qty
            WHERE product_id = $id
            AND warehouse_id = $warehouse_id
        ";

        if (!mysqli_query($conn, $query2)) {
            throw new Exception(mysqli_error($conn));
        }

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Product update failed: " . $e->getMessage());
    }
}


/* =========================
   STOCK FUNCTIONS (BATCH)
========================= */
function addStockBatch($conn, $warehouse_id, $product_id, $quantity, $production_date, $expiration_date)
{

    $query = "
        INSERT INTO tbl_warehouse_stock
        (warehouse_id, product_id, quantity, production_date, expiration_date)
        VALUES
        ($warehouse_id, $product_id, $quantity, '$production_date', '$expiration_date')
    ";

    return mysqli_query($conn, $query);
}

function getProductsStats($conn)
{
    $query = "
SELECT
    COUNT(*) AS total_products,
    SUM(quantity) AS total_stock,
    SUM(weight) AS total_weight,
    SUM(CASE WHEN quantity <= 10 THEN 1 ELSE 0 END) AS low_stock
FROM (
    SELECT
        p.product_id,
        COUNT(sb.box_id) AS quantity,
        COALESCE(SUM(sb.box_weight), 0) AS weight
    FROM tbl_products p
    LEFT JOIN tbl_stock_boxes sb
        ON p.product_id = sb.product_id
    GROUP BY p.product_id
) AS product_totals
    ";

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getLowStockProducts($conn)
{
    $query = "
        SELECT
            p.product_id,
            p.product_name,
            IFNULL(SUM(ws.quantity), 0) AS quantity
        FROM tbl_products p
        LEFT JOIN tbl_warehouse_stock ws
            ON p.product_id = ws.product_id
        GROUP BY p.product_id
        HAVING quantity <= 10
        ORDER BY quantity ASC
        LIMIT 5
    ";

    return mysqli_query($conn, $query);
}

function getRecentStockActivity($conn, $limit = 5)
{
    $query = "
        SELECT
            p.product_name,
            ws.quantity,
            ws.created_at
        FROM tbl_warehouse_stock ws
        JOIN tbl_products p
            ON ws.product_id = p.product_id
        ORDER BY ws.created_at DESC
        LIMIT $limit
    ";

    return mysqli_query($conn, $query);
}

function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM tbl_products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getProductStats($conn, $product_id)
{
    $sql = "
        SELECT 
            SUM(box_weight) AS total_inventory,

            SUM(CASE WHEN status = 'available' THEN box_weight ELSE 0 END) AS available,

            SUM(CASE WHEN status = 'reserved' THEN box_weight ELSE 0 END) AS reserved,

            SUM(CASE WHEN condition_status = 'damaged' THEN box_weight ELSE 0 END) AS spoiled,

            SUM(
                CASE 
                    WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) 
                    AND status = 'available'
                    THEN box_weight 
                    ELSE 0 
                END
            ) AS expiring

        FROM tbl_stock_boxes
        WHERE product_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

// Add similar functions for batches, movements, orders, expiring alerts
function getProductBatches($conn, $product_id)
{
    $sql = "
        SELECT 
            batch_code AS batch_id,
            DATE(created_at) AS arr_date,
            DATE(expiry_date) AS expiration_date,
            SUM(box_weight) AS qty,
            pallet_code AS storage_info,

            CASE 
                WHEN expiry_date <= CURDATE() THEN 'Expired'
                WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'Expiring'
                ELSE 'Safe'
            END AS status

        FROM tbl_stock_boxes
        WHERE product_id = ?
        AND status = 'available'

        GROUP BY batch_code, expiry_date, pallet_code
        ORDER BY expiry_date ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result();
}

function getExpiringBatches($conn, $product_id)
{
    $sql = "
        SELECT 
            batch_code AS batch_id,
            DATEDIFF(expiry_date, CURDATE()) AS days_left,
            SUM(box_weight) AS qty

        FROM tbl_stock_boxes
        WHERE product_id = ?
        AND status = 'available'
        AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY)

        GROUP BY batch_code, expiry_date
        ORDER BY expiry_date ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result();
}

function getProductInventoryMovements($conn, $product_id)
{
    $sql = "
        SELECT 
            box_id,
            delivery_item_id,
            batch_code,
            box_weight,
            status,
            created_at,

            CASE 
                WHEN status = 'available' THEN 'IN'
                WHEN status = 'reserved' THEN 'OUT'
                ELSE status
            END AS movement_type

        FROM tbl_stock_boxes
        WHERE product_id = ?

        ORDER BY created_at DESC
        LIMIT 50
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result();
}

function getActiveOrdersForProduct($conn, $product_id)
{
    $sql = "
        SELECT 
            delivery_item_id,
            batch_code,
            SUM(box_weight) AS reserved_weight,
            COUNT(box_id) AS total_boxes

        FROM tbl_stock_boxes
        WHERE product_id = ?
        AND status = 'reserved'

        GROUP BY delivery_item_id, batch_code
        ORDER BY delivery_item_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result();
}