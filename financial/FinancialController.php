<?php
require_once 'FinancialRepository.php';
require_once 'FinancialService.php';
require_once '../config/database_conn.php';

header('Content-Type: application/json');

try {


    $repo = new FinancialRepository($databaseconn);
    $service = new FinancialService($repo);

    $service->addItem(69, 12, 5);

    echo "Test executed.";

    $action = $_POST['action'] ?? null;

    switch ($action) {

        case 'add_item':

            $jobOrderId = $_POST['job_order_id'] ?? null;
            $productId  = $_POST['product_id'] ?? null;
            $quantity   = $_POST['quantity'] ?? null;

            if (!$jobOrderId || !$productId || !$quantity) {
                throw new Exception("Missing required fields.");
            }

            $service->addItem($jobOrderId, $productId, $quantity);

            echo json_encode([
                'success' => true,
                'message' => 'Item added successfully.'
            ]);
            break;

        case 'recompute_totals':

            $jobOrderId = $_POST['job_order_id'] ?? null;

            if (!$jobOrderId) {
                throw new Exception("Job order ID is required.");
            }

            $service->recomputeTotals($jobOrderId);

            echo json_encode([
                'success' => true,
                'message' => 'Totals recomputed.'
            ]);
            break;

        default:
            throw new Exception("Invalid action.");
    }
} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
