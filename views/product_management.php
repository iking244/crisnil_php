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

</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <main class="main-content p-4">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-title">Products Management</h1>

                <button class="export-btn btn btn-primary">
                    <i class="fa fa-download"></i> Export
                </button>
            </div>
            <div class="action-bar mb-4">
                <div class="action-left">
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


            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card stat-blue">
                        <div class="stat-header">
                            <i class="fa fa-box"></i>
                            <span>Total Products</span>
                        </div>
                        <div class="stat-value">
                            <?= $stats['total_products'] ?? 0 ?>
                        </div>
                    </div>
                </div>


                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">Total Stock</div>
                        <div class="stat-value">
                            <?= number_format($stats['total_stock'] ?? 0) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="stat-title">Low Stock Items</div>
                        <div class="stat-value">
                            <?= $stats['low_stock'] ?? 0 ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-title">Total Weight</div>
                        <div class="stat-value">
                            <?= number_format($stats['total_weight'] ?? 0, 2) ?> kg
                        </div>
                    </div>
                </div>

            </div>


            <!-- Search -->
            <div class="search-wrapper mb-4">
                <input type="text" id="searchInput" class="search-input"
                    placeholder="Search by Product Code or Name...">
                <i class="fa fa-search search-icon"></i>
            </div>

            <form method="GET" class="mb-3">
                <label><strong>Select Warehouse:</strong></label>
                <select name="warehouse_id" onchange="this.form.submit()" class="form-control">
                    <option value="0" <?= $warehouse_id == 0 ? 'selected' : '' ?>>
                        All Warehouses
                    </option>FS

                    <?php while ($w = mysqli_fetch_assoc($warehouses)): ?>
                        <option value="<?= $w['warehouse_id'] ?>"
                            <?= $warehouse_id == $w['warehouse_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($w['warehouse_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

            </form>


            <!-- Table -->
            <div class="table-container">
                <table class="orders-table" id="ordersTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Unit</th>
                            <th>Quantity</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" class="row-check"></td>

                                <td><strong><?= $row['product_code'] ?></strong></td>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['weight'] ?></td>

                                <td>
                                    <?php
                                    if ($row['quantity'] <= 10) {
                                        echo '<span class="status-badge pending">Low Stock</span>';
                                    } else {
                                        echo '<span class="status-badge completed">Available</span>';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <i class="fa fa-pencil edit-product"
                                        data-id="<?= $row['product_id'] ?>"
                                        data-code="<?= $row['product_code'] ?>"
                                        data-name="<?= htmlspecialchars($row['product_name']) ?>"
                                        data-qty="<?= $row['quantity'] ?>"
                                        data-unit-id="<?= $row['unit_id'] ?>"
                                        data-weight-per-unit="<?= $row['weight_per_unit'] ?>"
                                        data-units-per-pallet="<?= $row['units_per_pallet'] ?>"
                                        title="Edit"></i>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer mt-4">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                        class="view-btn"
                        style="<?= ($i == $page) ? 'background:#1e3fa3;color:white;' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
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

    <!-- =========================
 CREATE PRODUCT MODAL
========================= -->
    <div class="modal fade" id="createProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form action="../controllers/products_controller.php?action=create" method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-box me-2 text-primary"></i>
                            Create Product
                        </h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <!-- LEFT COLUMN -->
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" name="product_code"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Product Name</label>
                                    <input type="text" name="product_name"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Unit</label>
                                    <select name="unit_id" class="form-control" required>
                                        <option value="">Select unit</option>
                                        <?php
                                        mysqli_data_seek($units, 0);
                                        while ($u = mysqli_fetch_assoc($units)): ?>
                                            <option value="<?= $u['unit_id'] ?>">
                                                <?= htmlspecialchars($u['unit_name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label class="form-label">Initial Quantity</label>
                                    <input type="number" name="quantity"
                                        class="form-control" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Weight per Unit (kg)</label>
                                    <input type="number" step="0.01"
                                        name="weight_per_unit"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Units per Pallet</label>
                                    <input type="number"
                                        name="units_per_pallet"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Production Date</label>
                                    <input type="date" name="production_date"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Expiration Date</label>
                                    <input type="date" name="expiration_date"
                                        class="form-control" required>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa fa-check me-1"></i> Create Product
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <!-- =========================
 EDIT PRODUCT MODAL
========================= -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="../controllers/products_controller.php?action=update" method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-pen me-2 text-primary"></i>
                            Edit Product
                        </h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>


                    <div class="modal-body">

                        <input type="hidden" name="product_id" id="editProductId">

                        <div class="row">

                            <!-- LEFT COLUMN -->
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" id="editCode"
                                        name="product_code"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" id="editName"
                                        name="product_name"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Unit</label>
                                    <select id="editUnit"
                                        name="unit_id"
                                        class="form-control" required>
                                        <option value="">Select unit</option>
                                        <?php
                                        mysqli_data_seek($units, 0);
                                        while ($u = mysqli_fetch_assoc($units)): ?>
                                            <option value="<?= $u['unit_id'] ?>">
                                                <?= htmlspecialchars($u['unit_name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                            </div>

                            <!-- RIGHT COLUMN -->
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" id="editQty"
                                        name="quantity"
                                        class="form-control" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Weight per Unit (kg)</label>
                                    <input type="number" step="0.01"
                                        id="editWeightPerUnit"
                                        name="weight_per_unit"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Units per Pallet</label>
                                    <input type="number"
                                        id="editUnitsPerPallet"
                                        name="units_per_pallet"
                                        class="form-control" min="1" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Production Date</label>
                                    <input type="date"
                                        name="production_date"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Expiration Date</label>
                                    <input type="date"
                                        name="expiration_date"
                                        class="form-control" required>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>


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

    <div class="modal fade" id="addStockModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="../controllers/stock_controller.php?action=add" method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-box me-2 text-primary"></i>
                            Add Stock Batch
                        </h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Product</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">Select product</option>
                                <?php
                                mysqli_data_seek($products, 0);
                                while ($p = mysqli_fetch_assoc($products)): ?>
                                    <option value="<?= $p['product_id'] ?>">
                                        <?= htmlspecialchars($p['product_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Production Date</label>
                            <input type="date" name="production_date"
                                class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration_date"
                                class="form-control" required>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Add Stock
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>




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