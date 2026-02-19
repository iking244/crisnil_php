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
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm">
                        <small>Total Products</small>
                        <h3><?= $stats['total_products'] ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 shadow-sm">
                        <small>Total Stock</small>
                        <h3><?= number_format($stats['total_stock']) ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 shadow-sm">
                        <small>Low Stock Items</small>
                        <h3 class="text-warning"><?= $stats['low_stock'] ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 shadow-sm">
                        <small>Total Weight</small>
                        <h3><?= number_format($stats['total_weight']) ?> kg</h3>
                    </div>
                </div>
            </div>

            <!-- Low Stock Mini Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Low Stock Products</strong>
                    <a href="product_list.php" class="btn btn-sm btn-outline-primary">
                        View All Products
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($lowStockProducts) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($lowStockProducts)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td><?= $row['quantity'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center p-3">
                                        No low stock products.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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