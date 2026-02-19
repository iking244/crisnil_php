<?php
include "../controllers/products_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Overview - CRISNIL</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/orders.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">

</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <main class="main-content p-4">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Products Overview</h1>

                <div class="d-flex gap-2">
                    <a href="product_management.php" class="btn btn-outline-dark">
                        <i class="fa fa-list"></i> View All Products
                    </a>
                    <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createProductModal">
                        <i class="fa fa-plus"></i> Create Product
                    </button>

                    <button class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#addStockModal">
                        <i class="fa fa-box"></i> Add Stock
                    </button>


                </div>
            </div>

            <!-- Inventory Banner -->
            <?php if ($stats['low_stock'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= $stats['low_stock'] ?> products are low on stock.
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i>
                    Inventory is healthy. No low stock items.
                </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row mb-4 g-3">

                <div class="col-md-3">
                    <div class="stat-card stat-blue d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title">Total Products</div>
                            <div class="stat-value"><?= $stats['total_products'] ?></div>
                        </div>
                        <i class="fa fa-box fa-2x text-white opacity-75"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card stat-green d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title">Total Stock</div>
                            <div class="stat-value"><?= number_format($stats['total_stock']) ?></div>
                        </div>
                        <i class="fa fa-warehouse fa-2x text-white opacity-75"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card stat-orange d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title">Low Stock Items</div>
                            <div class="stat-value"><?= $stats['low_stock'] ?></div>
                        </div>
                        <i class="fa fa-exclamation-triangle fa-2x text-white opacity-75"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card stat-gray d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title">Total Weight</div>
                            <div class="stat-value"><?= number_format($stats['total_weight']) ?> kg</div>
                        </div>
                        <i class="fa fa-weight-hanging fa-2x text-white opacity-75"></i>
                    </div>
                </div>

            </div>


            <div class="row mt-4 g-4">

                <!-- Low Stock Column (Main Focus) -->
                <div class="col-lg-7">
                    <div class="card shadow-sm h-100">
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

                <!-- Recent Activity Column -->
                <div class="col-lg-5">
                    <div class="card shadow-sm h-100">
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/table.js"></script>
    <script src="../scripts/products.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>


</body>

</html>