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

/* =========================
   SALES TREND (7 DAYS)
========================= */

$salesTrend = $financialService->getSalesTrend();

$salesTrendLabels = json_encode($salesTrend['labels']);
$salesTrendData   = json_encode($salesTrend['data']);

$total_products = getTotalProducts($databaseconn);
$product_distribution = getProductDistribution($databaseconn);
$low_stock = getLowStockCount($databaseconn);
$active_deliveries = getActiveDeliveries($databaseconn);
$delivery_distribution = getDeliveryDistribution($databaseconn);
$activity_result = getRecentActivity($databaseconn);
$today_notifications = getTodayNotifications($databaseconn);
