<?php

/* =========================
   GET ALL WAREHOUSES
========================= */
function getAllWarehouses($conn)
{
    return mysqli_query($conn, "
        SELECT warehouse_id, warehouse_name
        FROM tbl_warehouses
        ORDER BY warehouse_name ASC
    ");
}

/* =========================
   GET ALL PRODUCTS
========================= */
function getAllProducts($conn)
{
    return mysqli_query($conn, "
        SELECT 
            p.product_id,
            p.product_code,
            p.product_name,
            u.unit_name AS unit,
            IFNULL(SUM(ws.quantity), 0) AS quantity,
            IFNULL(SUM(ws.quantity) * p.weight_per_unit, 0) AS weight,
            IFNULL(FLOOR(SUM(ws.quantity) / p.units_per_pallet), 0) AS pallets
        FROM tbl_products p
        LEFT JOIN tbl_units u ON p.unit_id = u.unit_id
        LEFT JOIN tbl_warehouse_stock ws ON p.product_id = ws.product_id
        GROUP BY p.product_id
        ORDER BY p.product_name ASC
    ");
}

function getAllProductsName($conn)
{
    return mysqli_query($conn, "
        SELECT product_id, product_name 
        FROM tbl_products 
        ORDER BY product_name ASC
    ");
}

/* =========================
   COUNT PRODUCTS
========================= */
function countAllProducts($conn)
{
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_products");
    return mysqli_fetch_assoc($res)['total'];
}

/* =========================
   PAGINATED PRODUCTS (BOX-BASED)
========================= */
function getProductsPaginated($conn, $warehouse_id, $limit, $offset)
{
    $warehouseFilter = ($warehouse_id == 0)
        ? ""
        : "AND sb.warehouse_id = $warehouse_id";

    return mysqli_query($conn, "
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
            $warehouseFilter

        GROUP BY p.product_id, p.product_code, p.product_name
        ORDER BY p.product_name ASC
        LIMIT $limit OFFSET $offset
    ");
}

/* =========================
   CREATE PRODUCT
========================= */
function createProduct($conn, $warehouse_id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet, $production_date, $expiration_date)
{
    mysqli_begin_transaction($conn);

    try {
        $check = mysqli_query($conn, "
            SELECT product_id FROM tbl_products 
            WHERE product_code = '$code' LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            throw new Exception("duplicate_code");
        }

        mysqli_query($conn, "
            INSERT INTO tbl_products 
            (product_code, product_name, unit_id, weight_per_unit, units_per_pallet)
            VALUES ('$code', '$name', $unit_id, $weight_per_unit, $units_per_pallet)
        ");

        $product_id = mysqli_insert_id($conn);

        mysqli_query($conn, "
            INSERT INTO tbl_warehouse_stock
            (warehouse_id, product_id, quantity, production_date, expiration_date)
            VALUES
            ($warehouse_id, $product_id, $qty, '$production_date', '$expiration_date')
        ");

        mysqli_commit($conn);
        return ["success" => true];
    } catch (Exception $e) {
        mysqli_rollback($conn);

        return [
            "success" => false,
            "error" =>
            $e->getMessage() === "duplicate_code"
                ? "Product code already exists."
                : $e->getMessage()
        ];
    }
}

/* =========================
   UPDATE PRODUCT
========================= */
function updateProduct($conn, $warehouse_id, $id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet)
{
    mysqli_begin_transaction($conn);

    try {
        $code = mysqli_real_escape_string($conn, $code);
        $name = mysqli_real_escape_string($conn, $name);

        mysqli_query($conn, "
            UPDATE tbl_products
            SET 
                product_code = '$code',
                product_name = '$name',
                unit_id = $unit_id,
                weight_per_unit = $weight_per_unit,
                units_per_pallet = $units_per_pallet
            WHERE product_id = $id
        ");

        mysqli_query($conn, "
            UPDATE tbl_warehouse_stock
            SET quantity = $qty
            WHERE product_id = $id
            AND warehouse_id = $warehouse_id
        ");

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("Update failed: " . $e->getMessage());
    }
}

/* =========================
   LOW STOCK (FIXED)
========================= */
function getLowStockProducts($conn)
{
    return mysqli_query($conn, "
        SELECT
            p.product_id,
            p.product_name,

            COUNT(
                CASE 
                    WHEN sb.status = 'available' 
                    AND sb.condition_status = 'good'
                    THEN 1
                END
            ) AS quantity

        FROM tbl_products p
        LEFT JOIN tbl_stock_boxes sb
            ON p.product_id = sb.product_id

        GROUP BY p.product_id, p.product_name
        HAVING quantity <= 10
        ORDER BY quantity ASC
        LIMIT 5
    ");
}

/* =========================
   PRODUCT STATS (FINAL)
========================= */
function getProductStats($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT 
            IFNULL(SUM(box_weight), 0) AS total_inventory,

            IFNULL(SUM(CASE 
                WHEN status = 'available' AND condition_status = 'good'
                THEN box_weight END), 0) AS available,

            IFNULL(SUM(CASE 
                WHEN status = 'reserved' AND condition_status = 'good'
                THEN box_weight END), 0) AS reserved,

            IFNULL(SUM(CASE 
                WHEN condition_status = 'damaged'
                THEN box_weight END), 0) AS spoiled,

            IFNULL(SUM(CASE 
                WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
                AND status = 'available' AND condition_status = 'good'
                THEN box_weight END), 0) AS expiring,

            IFNULL(SUM(CASE 
                WHEN expiry_date < CURDATE()
                THEN box_weight END), 0) AS expired,

            COUNT(CASE 
                WHEN status = 'available' AND condition_status = 'good'
                THEN 1 END) AS available_boxes

        FROM tbl_stock_boxes
        WHERE product_id = ?
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
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

                -- ✅ Only count usable boxes
                COUNT(
                    CASE 
                        WHEN sb.status = 'available'
                        AND sb.condition_status = 'good'
                        THEN 1
                    END
                ) AS quantity,

                -- ✅ Only sum usable weight
                COALESCE(SUM(
                    CASE 
                        WHEN sb.status = 'available'
                        AND sb.condition_status = 'good'
                        THEN sb.box_weight
                        ELSE 0
                    END
                ), 0) AS weight

            FROM tbl_products p

            LEFT JOIN tbl_stock_boxes sb
                ON p.product_id = sb.product_id

            GROUP BY p.product_id
        ) AS product_totals
    ";

    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

/* =========================
   RECENT ACTIVITY (MERGED FIX)
========================= */
function getRecentStockActivity($conn, $limit = 5)
{
    return mysqli_query($conn, "
        SELECT * FROM (

            -- STOCK IN
            SELECT 
                'IN' AS type,
                p.product_name,
                COUNT(s.box_id) AS quantity,
                SUM(s.box_weight) AS weight,
                s.created_at
            FROM tbl_stock_boxes s
            JOIN tbl_products p ON p.product_id = s.product_id
            GROUP BY s.created_at, p.product_id

            UNION ALL

            -- STOCK OUT
            SELECT 
                'OUT' AS type,
                p.product_name,
                di.qty AS quantity,
                di.total_weight AS weight,
                di.created_at
            FROM tbl_delivery_items di
            JOIN tbl_products p ON p.product_id = di.product_id

        ) AS combined

        ORDER BY created_at DESC
        LIMIT $limit
    ");
}

/* =========================
   GET PRODUCT BY ID
========================= */
function getProductById($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            u.unit_name AS unit
        FROM tbl_products p
        LEFT JOIN tbl_units u 
            ON p.unit_id = u.unit_id
        WHERE p.product_id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}

/* =========================
   PRODUCT BATCHES
========================= */
function getProductBatches($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT 
            batch_code AS batch_id,
            DATE(created_at) AS prod_date,
            DATE(created_at) AS arr_date,
            expiry_date AS expiration_date,

            COUNT(box_id) AS qty,

            CASE
                WHEN expiry_date < CURDATE() THEN 'Expired'
                WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) THEN 'Expiring'
                ELSE 'Safe'
            END AS status,

            CONCAT('Pallet ', pallet_id) AS storage_info

        FROM tbl_stock_boxes
        WHERE product_id = ?

        GROUP BY batch_code, expiry_date, pallet_id
        ORDER BY expiry_date ASC
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* =========================
   INVENTORY MOVEMENTS
========================= */
function getProductInventoryMovements($conn, $product_id, $limit = 20)
{
    $stmt = $conn->prepare("
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
        LIMIT $limit
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result();
}

/* =========================
   ACTIVE ORDERS
========================= */
function getActiveOrdersForProduct($conn, $product_id)
{
    return mysqli_query($conn, "
        SELECT 
            0 AS delivery_item_id,
            0 AS qty,
            0 AS total_weight,
            NOW() AS created_at,
            'N/A' AS dr_number
        LIMIT 0
    ");
}

/* =========================
   EXPIRING BATCHES
========================= */
function getExpiringBatches($conn, $product_id)
{
    $stmt = $conn->prepare("
        SELECT 
            batch_code AS batch_id,

            COUNT(box_id) AS qty,

            DATEDIFF(expiry_date, CURDATE()) AS days_left

        FROM tbl_stock_boxes

        WHERE product_id = ?
        AND expiry_date BETWEEN CURDATE() 
        AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)

        GROUP BY batch_code, expiry_date

        ORDER BY expiry_date ASC
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getBatchInventoryPaginated($conn, $warehouse_id, $limit, $offset)
{
    $warehouseFilter = ($warehouse_id == 0)
        ? ""
        : "AND sb.warehouse_id = $warehouse_id";

    return mysqli_query($conn, "
        SELECT
            sb.batch_code,

            p.product_id,
            p.product_name,

            pal.pallet_code,

            sb.expiry_date,

            COUNT(sb.box_id) AS quantity,

            SUM(sb.box_weight) AS weight,
                
            CASE
                WHEN sb.expiry_date < CURDATE()
                    THEN 'Expired'
                WHEN sb.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    THEN 'Expiring Soon'
                ELSE 'Safe'
                END AS batch_status

        FROM tbl_stock_boxes sb

        LEFT JOIN tbl_products p
            ON sb.product_id = p.product_id

        LEFT JOIN tbl_pallets pal
            ON sb.pallet_id = pal.pallet_id

        WHERE 1=1
        $warehouseFilter

        GROUP BY 
            sb.batch_code,
            p.product_name,
            pal.pallet_code,
            sb.expiry_date

        ORDER BY sb.created_at DESC

        LIMIT $limit OFFSET $offset
    ");
}
