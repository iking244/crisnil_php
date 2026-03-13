<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

include "../config/database_conn.php";
include "../models/products_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

if ($_GET['action'] == "get_delivery_by_dr") {

    $dr = $_GET['dr'];

$query = "
    SELECT 
        di.delivery_item_id,
        di.product_id,
        p.product_name,
        di.qty,
        di.unit,
        di.total_weight,
        di.price_per_kg,
        di.total_amount,
        dr.delivery_receipt_id,
        dr.dr_number
    FROM tbl_delivery_receipts dr
    JOIN tbl_delivery_items di 
        ON dr.delivery_receipt_id = di.delivery_receipt_id
    JOIN tbl_products p
        ON di.product_id = p.product_id
    WHERE dr.dr_number = ?
";

    $stmt = $databaseconn->prepare($query);
    $stmt->bind_param("s", $dr);
    $stmt->execute();

    $result = $stmt->get_result();

    $items = [];
    $delivery_id = null;

    while ($row = $result->fetch_assoc()) {

        $delivery_id = $row['delivery_receipt_id'];

        $items[] = [
            "delivery_item_id" => $row['delivery_item_id'],
            "product_id" => $row['product_id'],
            "product_name" => $row['product_name'],
            "qty" => $row['qty'],
            "unit" => $row['unit'],
            "total_weight" => $row['total_weight'],
            "price_per_kg" => $row['price_per_kg'],
            "total_amount" => $row['total_amount']
        ];
    }

    echo json_encode([
        "delivery_receipt_id" => $delivery_id,
        "items" => $items
    ]);

    exit();
}

if ($_GET['action'] == "add_delivery") {

    try {

        // Start transaction
        $databaseconn->begin_transaction();

        $dr_number = $_POST['dr_number'];
        $warehouse_id = $_POST['warehouse_id'];

        $query = "SELECT delivery_receipt_id 
          FROM tbl_delivery_receipts 
          WHERE dr_number = ?";

        $stmt = $databaseconn->prepare($query);
        $stmt->bind_param("s", $dr_number);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode([
                "status" => "error",
                "message" => " Delivery Receipt number already exists."
            ]);
            exit();
        }

        // Insert Delivery Receipt
        $query = "INSERT INTO tbl_delivery_receipts 
                  (dr_number, warehouse_id) 
                  VALUES (?, ?)";

        $stmt = $databaseconn->prepare($query);
        $stmt->bind_param("si", $dr_number, $warehouse_id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert delivery receipt");
        }

        $delivery_receipt_id = $databaseconn->insert_id;

        // Arrays from form
        $products = $_POST['product_id'];
        $qtys = $_POST['qty'];
        $units = $_POST['unit'];
        $weights = $_POST['weight'];
        $prices = $_POST['price'];
        $amounts = $_POST['amount'];

        $query = "INSERT INTO tbl_delivery_items 
                  (delivery_receipt_id, product_id, qty, unit, total_weight, price_per_kg, total_amount)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $databaseconn->prepare($query);

        for ($i = 0; $i < count($products); $i++) {

            $product_id = $products[$i];
            $qty = $qtys[$i];
            $unit = $units[$i];
            $weight = $weights[$i];
            $price = $prices[$i];
            $amount = $amounts[$i];

            $stmt->bind_param(
                "iiisddd",
                $delivery_receipt_id,
                $product_id,
                $qty,
                $unit,
                $weight,
                $price,
                $amount
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert delivery item");
            }
        }

        // Commit transaction
        $databaseconn->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Delivery saved successfully"
        ]);
        exit();
    } catch (Exception $e) {

        // Rollback everything
        $databaseconn->rollback();

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}

if ($_GET['action'] == "update_delivery") {

    $delivery_id = $_POST['delivery_receipt_id'];
    $dr_number = $_POST['dr_number'];

    $products = $_POST['product_id'];
    $qtys = $_POST['qty'];
    $units = $_POST['unit'];
    $weights = $_POST['weight'];
    $prices = $_POST['price'];
    $amounts = $_POST['amount'];
    $item_ids = $_POST['item_id'];

    $databaseconn->begin_transaction();

    try {

        // update DR number
        $query = "
            UPDATE tbl_delivery_receipts
            SET dr_number = ?
            WHERE delivery_receipt_id = ?
        ";

        $stmt = $databaseconn->prepare($query);
        $stmt->bind_param("si", $dr_number, $delivery_id);
        $stmt->execute();


        for ($i = 0; $i < count($products); $i++) {

            $product = $products[$i];
            $qty = $qtys[$i];
            $unit = $units[$i];
            $weight = $weights[$i];
            $price = $prices[$i];
            $amount = $amounts[$i];
            $item_id = $item_ids[$i];

            // update existing item
            if (!empty($item_id)) {

                $query = "
                    UPDATE tbl_delivery_items
                    SET product_id=?, qty=?, unit=?, 
                        total_weight=?, price_per_kg=?, total_amount=?
                    WHERE delivery_item_id=?
                ";

                $stmt = $databaseconn->prepare($query);

                $stmt->bind_param(
                    "iisdddi",
                    $product,
                    $qty,
                    $unit,
                    $weight,
                    $price,
                    $amount,
                    $item_id
                );

                $stmt->execute();
            }

            // insert new item
            else {

                $query = "
                    INSERT INTO tbl_delivery_items
                    (delivery_receipt_id, product_id, qty, unit, total_weight, price_per_kg, total_amount)
                    VALUES (?,?,?,?,?,?,?)
                ";

                $stmt = $databaseconn->prepare($query);

                $stmt->bind_param(
                    "iiisddd",
                    $delivery_id,
                    $product,
                    $qty,
                    $unit,
                    $weight,
                    $price,
                    $amount
                );

                $stmt->execute();
            }
        }

        $databaseconn->commit();

        echo json_encode([
            "status" => "success"
        ]);
    } catch (Exception $e) {

        $databaseconn->rollback();

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }

    exit();
}
