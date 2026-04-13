<?php
session_start();
include "../config/database_conn.php";
include "../models/dashboard_model.php";
require_once "../financial/FinancialRepository.php";
require_once "../financial/FinancialService.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

/* =========================
   INIT FINANCIAL LAYER
========================= */

$financialRepo = new FinancialRepository($databaseconn);
$financialService = new FinancialService($financialRepo);

/* =========================
   DASHBOARD METRICS
========================= */

$metrics = $financialService->getDashboardMetrics();

$sales_today      = $metrics['sales_today'];
$orders_today     = $metrics['orders_today'];
$monthly_revenue  = $metrics['monthly_revenue'];
$pending_orders = $metrics['pending_orders'];

/* =========================
   SALES TREND (7 DAYS)
========================= */

$salesTrend = getSalesTrendFromDeliveries($databaseconn);
$salesComparison = $financialService->getSalesComparison();
$salesTrendLabels = json_encode($salesTrend['labels']);
$salesTrendData   = json_encode($salesTrend['data']);

$lowStockItems = getLowStockItems($databaseconn);

$recentDeliveries = getRecentDeliveries($databaseconn);

$total_products = getTotalProducts($databaseconn);
$product_distribution = getProductDistribution($databaseconn);
$low_stock_count = getLowStockCount($databaseconn);
$active_deliveries = getActiveDeliveries($databaseconn);
$stockMovement = getStockMovement($databaseconn);
$stockIn  = $stockMovement['stock_in'];
$stockOut = $stockMovement['stock_out'];
$delivery_distribution = getDeliveryDistribution($databaseconn);
$activity_result = getRecentActivity($databaseconn);
$today_notifications = getTodayNotifications($databaseconn);

$query = mysqli_query($databaseconn, "
    SELECT SUM(di.total_amount) as total 
    FROM tbl_delivery_items di
    INNER JOIN tbl_delivery_receipts dr
        ON dr.delivery_receipt_id = di.delivery_receipt_id
    WHERE DATE(dr.created_at) = CURDATE()
");

$row = mysqli_fetch_assoc($query);
$sales_today = $row['total'] ?? 0;