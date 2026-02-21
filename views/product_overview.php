<?php
include "../controllers/products_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Overview - CRISNIL</title>

    <!-- Core Styles -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/products/products.css">

    <!-- External Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Additional Styles -->
    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">
    <div class="container-fluid">

        <!-- ================= HEADER ================= -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Products Overview</h1>

            <div class="d-flex gap-2">
                <a href="product_management.php" class="btn btn-outline-dark">
                    <i class="fa fa-list me-1"></i> View All Products
                </a>

                <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createProductModal">
                    <i class="fa fa-plus me-1"></i> Create Product
                </button>

                <button class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#addStockModal">
                    <i class="fa fa-box me-1"></i> Add Stock
                </button>
            </div>
        </div>

        <!-- ================= INVENTORY ALERT ================= -->
        <?php if ($stats['low_stock'] > 0): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle me-1"></i>
                <?= $stats['low_stock'] ?> products are low on stock.
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle me-1"></i>
                Inventory is healthy. No low stock items.
            </div>
        <?php endif; ?>

        <!-- ================= KPI CARDS ================= -->
        <div class="row mb-4 g-3">

            <div class="col-md-3">
                <div class="kpi-card blue d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Products</h6>
                        <h3><?= $stats['total_products'] ?></h3>
                    </div>
                    <i class="fa fa-box fa-2x text-white opacity-75"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card green d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Stock</h6>
                        <h3><?= number_format($stats['total_stock']) ?></h3>
                    </div>
                    <i class="fa fa-warehouse fa-2x text-white opacity-75"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card orange d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Low Stock Items</h6>
                        <h3><?= $stats['low_stock'] ?></h3>
                    </div>
                    <i class="fa fa-exclamation-triangle fa-2x text-white opacity-75"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card gray d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Weight</h6>
                        <h3><?= number_format($stats['total_weight']) ?> kg</h3>
                    </div>
                    <i class="fa fa-weight-hanging fa-2x text-white opacity-75"></i>
                </div>
            </div>

        </div>

        <!-- ================= LOWER PANELS ================= -->
        <div class="row mt-4 g-4">

            <!-- Low Stock Products -->
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Low Stock Products</h6>
                            <a href="product_list.php" class="btn btn-sm btn-outline-primary">
                                View All
                            </a>
                        </div>

                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($lowStockProducts && mysqli_num_rows($lowStockProducts) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($lowStockProducts)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                                            <td class="text-end text-danger fw-bold">
                                                <?= $row['quantity'] ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            No low stock products.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <!-- Recent Stock Activity -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-body">

                        <h6 class="mb-3">Recent Stock Activity</h6>

                        <ul class="list-group list-group-flush">
                            <?php if ($recentStock && mysqli_num_rows($recentStock) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($recentStock)): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($row['product_name']) ?></span>
                                        <span class="text-success fw-semibold">
                                            +<?= $row['quantity'] ?>
                                        </span>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="list-group-item text-center text-muted py-4">
                                    No recent activity.
                                </li>
                            <?php endif; ?>
                        </ul>

                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ================= MODALS ================= -->
<?php include 'modals/create_product_modal.php'; ?>
<?php include 'modals/add_stock_modal.php'; ?>

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/table.js"></script>
<script src="../scripts/products.js"></script>
<script src="../scripts/notif.js"></script>
<script src="../scripts/sidenav.js"></script>
<script src="../scripts/dropdown2.js"></script>

</body>
</html>