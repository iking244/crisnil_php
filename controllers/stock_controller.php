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

    $dr_number = $_POST['dr_number'];
    $warehouse_id = $_POST['warehouse_id'];

    // Insert Delivery Receipt
    $query = "INSERT INTO tbl_delivery_receipts 
              (dr_number, warehouse_id) 
              VALUES (?, ?)";

    $stmt = $databaseconn->prepare($query);
    $stmt->bind_param("si", $dr_number, $warehouse_id);
    $stmt->execute();

    $delivery_receipt_id = $conn->insert_id;

    // Arrays from form
    $products = $_POST['product_id'];
    $qtys = $_POST['qty'];
    $units = $_POST['unit'];
    $weights = $_POST['weight'];
    $prices = $_POST['price'];
    $amounts = $_POST['amount'];

    for ($i = 0; $i < count($products); $i++) {

        $product_id = $products[$i];
        $qty = $qtys[$i];
        $unit = $units[$i];
        $weight = $weights[$i];
        $price = $prices[$i];
        $amount = $amounts[$i];

        $query = "INSERT INTO tbl_delivery_items 
                  (delivery_receipt_id, product_id, qty, unit, total_weight, price_per_kg, total_amount)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);
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

        $stmt->execute();
    }

    header("Location: ../views/products_overview.php?success=delivery_added");
}