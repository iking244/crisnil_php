<?php
include "../config/database_conn.php";
include "../models/products_model.php";

/* =========================
   PAGINATION
========================= */
$limit = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$warehouse_id = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;

$page = max($page, 1);
$limit = max($limit, 1);

$offset = ($page - 1) * $limit;

/* =========================
   TOTAL COUNT
========================= */
$totalProducts = countAllProducts($databaseconn);
$totalPages = ceil($totalProducts / $limit);

/* =========================
   LOAD PRODUCTS
========================= */
$products = getProductsPaginated(
    $databaseconn,
    $warehouse_id,
    $limit,
    $offset
);
?>
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
            <?php while ($row = mysqli_fetch_assoc($products)): ?>
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
<div class="card-footer mt-3 d-flex justify-content-between align-items-center">
    <div>
        Page <?= $page ?> of <?= $totalPages ?>
    </div>

    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <button class="view-btn <?= $i == $page ? 'active' : '' ?>"
                onclick="loadProducts(<?= $i ?>)">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>
</div>