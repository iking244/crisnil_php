<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../controllers/dashboard_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Crisnil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/dashboard/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div class="main">
        <div class="admin_dashboard_main_ui container-fluid">

            <!-- PAGE HEADER -->
            <div class="dashboard-title mt-3">
                <h2>Dashboard Overview</h2>
                <p class="text-muted">Summary of today’s operations</p>
            </div>

            <!-- KPI ROW -->
            <div class="dashboard-kpi-row mt-3">
                <div class="row g-3">

                    <div class="col-md-2">
                        <div class="kpi-card blue">
                            <h6>Sales Today</h6>
                            <h3>₱ <?= number_format($sales_today, 2) ?></h3>
                            <span class="kpi-sub">
                                <?= $salesComparison >= 0 ? '+' : '' ?>
                                <?= $salesComparison ?>% vs yesterday
                            </span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="kpi-card dark">
                            <h6>Orders Today</h6>
                            <h3><?= $orders_today ?></h3>
                            <span class="kpi-sub"><?= $pending_orders ?> pending</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="kpi-card red">
                            <h6>Low Stock</h6>
                            <h3><?= $low_stock_count ?? 0 ?></h3>
                            <span class="kpi-sub">Needs restocking</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="kpi-card orange">
                            <h6>Expiring Soon</h6>
                            <h3>0</h3>
                            <span class="kpi-sub">Within 3 days</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="kpi-card green">
                            <h6>Active Deliveries</h6>
                            <h3><?= $active_deliveries ?? 0 ?></h3>
                            <span class="kpi-sub">In progress</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="kpi-card gray">
                            <h6>Monthly Revenue</h6>
                            <h3>₱ <?= number_format($monthly_revenue, 2) ?></h3>
                            <span class="kpi-sub">This month</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CHARTS -->
            <div class="row g-4 mt-4">

                <div class="col-lg-8">
                    <div class="chart-card">
                        <h5>Sales Trend (Last 7 Days)</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card">
                        <h5>Stock Movement</h5>
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- LOWER TABLES -->
            <div class="row g-4 mt-4">

                <div class="col-lg-6">
                    <div class="table-card">
                        <div class="card-header">
                            <h5>Low Stock Items</h5>
                            <a href="product_management.php" class="btn btn-sm btn-outline-danger">View All</a>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No low stock items
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="table-card">
                        <div class="card-header">
                            <h5>Recent Deliveries</h5>
                            <a href="logistics_dashboard.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        No recent deliveries
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>


    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../scripts/dashboard.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>

    <!-- Chart Placeholder Script -->
    <script>
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: <?= $salesTrendLabels ?>,
                    datasets: [{
                        label: 'Sales',
                        data: <?= $salesTrendData ?>,
                        borderColor: '#d32f2f',
                        fill: true,
                        backgroundColor: 'rgba(211,47,47,0.1)',
                        tension: 0.4
                    }]
                }
            });
        }

        const stockCtx = document.getElementById('stockChart');
        if (stockCtx) {
            new Chart(stockCtx, {
                type: 'bar',
                data: {
                    labels: ['Stock In', 'Stock Out'],
                    datasets: [{
                        data: [50, 32],
                        backgroundColor: ['#2e7d32', '#d32f2f']
                    }]
                }
            });
        }
    </script>

    <footer>
        <div class="footer1"></div>
    </footer>
    </div>


</body>

</html>