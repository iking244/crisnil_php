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
