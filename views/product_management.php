<?php 
include "../controllers/products_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - CRISNIL</title>

    <!-- Core CSS -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/products/products.css">
    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">
    <link rel="stylesheet" href="../styles/sticky_filter.css">

    <!-- External -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">
    <div class="container-fluid pt-2">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products Management</h1>

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

                <button class="btn btn-outline-dark">
                    <i class="fa fa-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Inventory Banner -->
        <?php if ($stats['low_stock'] > 0): ?>
            <div class="alert alert-warning mb-4">
                <i class="fa fa-exclamation-triangle"></i>
                <?= $stats['low_stock'] ?> products are low on stock.
                <a href="?filter=low" class="btn btn-sm btn-dark ms-2">
                    View Items
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-success mb-4">
                <i class="fa fa-check-circle"></i>
                Inventory is healthy. No low stock items.
            </div>
        <?php endif; ?>

        <!-- FILTER CARD -->
        <div class="card mb-4 sticky-filters">
            <div class="card-body py-3">

                <div class="d-flex flex-wrap align-items-center gap-3">

                    <!-- Search -->
                    <div class="search-wrapper">
                        <input type="text" id="searchInput" class="search-input"
                               placeholder="Search by Product Code or Name...">
                        <i class="fa fa-search search-icon"></i>
                    </div>

                    <!-- Warehouse -->
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

                    <!-- Page Size -->
                    <div class="d-flex align-items-center gap-2">
                        <span>Show</span>
                        <select id="pageSize" class="form-select form-select-sm" style="width:80px;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                        </select>
                        <span>entries</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="card">
            <div class="card-body">

                <div id="productsTableContainer"></div>

            </div>
        </div>

    </div>
</div>

<!-- Floating Action Button -->
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

<!-- Modals -->
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

</body>
</html>