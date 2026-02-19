<?php

/* =========================
   GET ALL PRODUCTS
========================= */
function getAllProducts($conn) {
    $query = "
        SELECT 
            p.product_id,
            p.product_code,
            p.product_name,
            u.unit_name AS unit,
            IFNULL(ws.quantity, 0) AS quantity,
            IFNULL(ws.quantity * p.weight_per_unit, 0) AS weight,
            IFNULL(FLOOR(ws.quantity / p.units_per_pallet), 0) AS pallets
        FROM tbl_products p
        LEFT JOIN tbl_units u
            ON p.unit_id = u.unit_id
        LEFT JOIN tbl_warehouse_stock ws 
            ON p.product_id = ws.product_id
        ORDER BY p.product_name ASC
    ";

    return mysqli_query($conn, $query);
}


function countAllProducts($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_products");
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getProductsPaginated($conn, $warehouse_id, $limit, $offset) {
    $query = "
        SELECT 
            p.product_id,
            p.product_code,
            p.product_name,
            p.unit_id,
            p.weight_per_unit,
            p.units_per_pallet,
            u.unit_name AS unit,
            IFNULL(ws.quantity, 0) AS quantity,
            IFNULL(ws.quantity * p.weight_per_unit, 0) AS weight,
            IFNULL(FLOOR(ws.quantity / p.units_per_pallet), 0) AS pallets
        FROM tbl_products p
        LEFT JOIN tbl_units u
            ON p.unit_id = u.unit_id
        LEFT JOIN tbl_warehouse_stock ws 
            ON p.product_id = ws.product_id
            AND ws.warehouse_id = $warehouse_id
        ORDER BY p.product_name ASC
        LIMIT $limit OFFSET $offset
    ";

    return mysqli_query($conn, $query);
}





/* =========================
   CREATE PRODUCT
========================= */
function createProduct($conn, $warehouse_id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet) {

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
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

        // Default warehouse

        // Insert stock (quantity only)
        $stockQuery = "
            INSERT INTO tbl_warehouse_stock
            (warehouse_id, product_id, quantity)
            VALUES
            ($warehouse_id, $product_id, $qty)
        ";

        if (!mysqli_query($conn, $stockQuery)) {
            throw new Exception(mysqli_error($conn));
        }

        // Commit if everything succeeded
        mysqli_commit($conn);

        return $product_id;

    } catch (Exception $e) {
        // Rollback on any error
        mysqli_rollback($conn);

        die("Product creation failed: " . $e->getMessage());
    }
}






/* =========================
   UPDATE PRODUCT
========================= */
function updateProduct($conn, $warehouse_id, $id, $code, $name, $unit_id, $qty, $weight_per_unit, $units_per_pallet) {

    mysqli_begin_transaction($conn);

    try {
        // Escape strings
        $code = mysqli_real_escape_string($conn, $code);
        $name = mysqli_real_escape_string($conn, $name);

        // Force numeric types
        $unit_id = (int)$unit_id;
        $qty = (int)$qty;
        $weight_per_unit = (float)$weight_per_unit;
        $units_per_pallet = (int)$units_per_pallet;
        $id = (int)$id;
        $warehouse_id = (int)$warehouse_id;

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


