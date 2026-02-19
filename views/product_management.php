<?php
include "../controllers/products_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - CRISNIL TRADING CORPORATION</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/orders.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">
    <link rel="stylesheet" href="../styles/sticky_filter.css">

</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <main class="main-content p-4">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Products Management</h1>

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
                    <button class="export-btn btn btn-primary">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </div>

            <?php if ($stats['low_stock'] > 0): ?>
                <div class="inventory-banner warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= $stats['low_stock'] ?> products are low on stock.
                    <a href="?filter=low" class="btn btn-sm btn-dark ms-2">
                        View Items
                    </a>
                </div>
            <?php else: ?>
                <div class="inventory-banner success">
                    <i class="fa fa-check-circle"></i>
                    Inventory is healthy. No low stock items.
                </div>
            <?php endif; ?>

            <!-- Sticky Filter Bar -->
            <div class="sticky-filters">
                <div class="filters-inner">

                    <!-- Search -->
                    <div class="search-wrapper">
                        <input type="text" id="searchInput" class="search-input"
                            placeholder="Search by Product Code or Name...">
                        <i class="fa fa-search search-icon"></i>
                    </div>

                    <!-- Warehouse filter -->
                    <form method="GET" class="warehouse-form">
                        <select name="warehouse_id"
                            onchange="this.form.submit()"
                            class="form-select">

                            <option value="0" <?= $warehouse_id == 0 ? 'selected' : '' ?>>
                                All Warehouses
                            </option>

                            <?php while ($w = mysqli_fetch_assoc($warehouses)): ?>
                                <option value="<?= $w['warehouse_id'] ?>"
                                    <?= $warehouse_id == $w['warehouse_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($w['warehouse_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>

                    <!-- Page size -->
                    <div class="page-size">
                        <label>Show</label>
                        <select id="pageSize" class="form-select">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                        <span>entries</span>
                    </div>

                </div>
            </div>


            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div id="productsTableContainer"></div>
                </div>
            </div>

            <!-- Floating Add Button -->
            <div class="fab-container">
                <div class="fab-options" id="fabOptions">
                    <button class="fab-option fab-green"
                        data-bs-toggle="modal"
                        data-bs-target="#createProductModal"
                        title="Create Product">
                        <i class="fa fa-tag"></i>
                    </button>

                    <button class="fab-option fab-blue"
                        data-bs-toggle="modal"
                        data-bs-target="#addStockModal"
                        title="Add Stock">
                        <i class="fa fa-box"></i>
                    </button>

                </div>

                <button class="fab-main" id="fabMain">
                    <i class="fa fa-plus"></i>
                </button>
            </div>


        </div>
    </main>



    <div class="modal fade" id="addActionModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content text-center p-4">

                <h5 class="mb-4 fw-semibold">Select Action</h5>

                <button class="btn btn-success w-100 mb-3 py-2"
                    data-bs-dismiss="modal"
                    data-bs-toggle="modal"
                    data-bs-target="#createProductModal">
                    <i class="fa fa-tag me-2"></i>
                    Create Product
                </button>

                <button class="btn btn-primary w-100 py-2"
                    data-bs-dismiss="modal"
                    data-bs-toggle="modal"
                    data-bs-target="#addStockModal">
                    <i class="fa fa-box me-2"></i>
                    Add Stock Batch
                </button>

            </div>

        </div>
    </div>

    <?php include 'modals/create_product_modal.php'; ?>
    <?php include 'modals/edit_product_modal.php'; ?>
    <?php include 'modals/add_stock_modal.php'; ?>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/table.js"></script>
    <script src="../scripts/products.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 2000">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Notification
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>



</body>

</html>