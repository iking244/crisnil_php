<?php
// views/product_details.php

session_start();

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../config/database_conn.php';
require_once __DIR__ . '/../models/products_model.php';

// Get product ID from URL
$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: ../views/product_management.php");
    exit();
}

// Fetch product details
$product = getProductById($databaseconn, $product_id);

if (!$product) {
    $_SESSION['error'] = "Product not found.";
    header("Location: ../views/product_management.php");
    exit();
}

// Fetch additional data (you'll need to add these functions to products_model.php)
$stats = getProductStats($databaseconn, $product_id);
$batches = getProductBatches($databaseconn, $product_id);
$inventory_movements = getProductInventoryMovements($databaseconn, $product_id);
$active_orders = getActiveOrdersForProduct($databaseconn, $product_id);
$expiring_alerts = getExpiringBatches($databaseconn, $product_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> - CRISNIL</title>

    <!-- Same styles as other pages -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/products/products.css">
    <link rel="stylesheet" href="../styles/product_details.css"> <!-- we'll create this -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Chart.js for stock level graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">
    <div class="container-fluid">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?= htmlspecialchars($product['product_name']) ?></h1>
            <div class="d-flex gap-2">
                <button class="btn btn-danger">
                    <i class="fa fa-box me-1"></i> Receive
                </button>
                <button class="btn btn-primary">
                    <i class="fa fa-truck me-1"></i> Dispatch
                </button>
                <button class="btn btn-outline-success">
                    <i class="fa fa-exchange-alt me-1"></i> Transfer Stock
                </button>
                <button class="btn btn-outline-warning">
                    <i class="fa fa-balance-scale me-1"></i> Adjust
                </button>
            </div>
        </div>

        <!-- Product Card -->
        <div class="card mb-4">
            <div class="card-body d-flex align-items-start gap-4">
                <!-- Photo -->
                <img src="<?= $product['image_url'] ?? '../imgs/placeholder-meat.jpg' ?>" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>" 
                     class="rounded" style="width: 180px; height: 180px; object-fit: cover;">

                <div class="flex-grow-1">
                    <h3 class="mb-1">
                        <?= htmlspecialchars($product['product_name']) ?>
                        <span class="badge bg-success ms-2">Healthy Stock</span>
                    </h3>
                    <p class="text-muted mb-2">
                        SKU: <?= htmlspecialchars($product['product_code']) ?> 
                        • Category: <?= htmlspecialchars($product['category'] ?? 'Fresh Beef') ?> 
                        • Supplier: <?= htmlspecialchars($product['supplier'] ?? 'Unknown') ?>
                    </p>
                    <p class="text-muted">
                        High-grade imported A5 Wagyu Ribeye cut. Requires strict cold chain maintenance at -2°C to 2°C. Vacuum sealed to maintain freshness and marbling integrity.
                    </p>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card text-center">
                    <div class="card-body">
                        <h6>Total Inventory</h6>
                        <h4><?= number_format($stats['total_inventory'] ?? 0) ?> kg</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h6>Available Stock</h6>
                        <h4><?= number_format($stats['available'] ?? 0) ?> kg</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h6>Reserved Stock</h6>
                        <h4><?= number_format($stats['reserved'] ?? 0) ?> kg</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <h6>Spoiled / Damaged</h6>
                        <h4><?= number_format($stats['spoiled'] ?? 0) ?> kg</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h6>Expiring Soon</h6>
                        <h4><?= number_format($stats['expiring'] ?? 0) ?> kg</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Level Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Stock Level Over Time</h6>
            </div>
            <div class="card-body">
                <canvas id="stockChart" height="100"></canvas>
            </div>
        </div>

        <!-- Perishability Alerts -->
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">Perishability Alerts (2 Action Needed)</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($expiring_alerts as $alert): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($alert['batch_id']) ?></strong><br>
                            <small>Expiring <?= $alert['days_left'] ?> days</small>
                        </div>
                        <span class="badge bg-warning"><?= number_format($alert['qty']) ?> kg</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Batch Tracking -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Batch Tracking & Cold Chain</h6>
                <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Dates (Prod & Arr)</th>
                                <th>Expiration</th>
                                <th>Qty (kg)</th>
                                <th>Status</th>
                                <th>Cold Storage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($batches as $batch): ?>
                                <tr>
                                    <td><?= htmlspecialchars($batch['batch_id']) ?></td>
                                    <td><?= htmlspecialchars($batch['prod_date'] . ' → ' . $batch['arr_date']) ?></td>
                                    <td><?= htmlspecialchars($batch['expiration_date']) ?></td>
                                    <td><?= number_format($batch['qty']) ?></td>
                                    <td><span class="badge bg-<?= $batch['status'] === 'Safe' ? 'success' : 'warning' ?>"><?= $batch['status'] ?></span></td>
                                    <td><?= htmlspecialchars($batch['storage_info']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- More sections: Pallet Inventory, Inventory Movement, Active Orders ... -->
        <!-- You can copy-paste similar structure from the screenshot -->

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Stock Level Chart (example data)
    const ctx = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'],
            datasets: [{
                label: 'Stock Level (kg)',
                data: [800, 700, 1200, 1100, 900, 1100, 1450],
                borderColor: '#dc3545',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(220, 53, 69, 0.1)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
</script>
</body>
</html>