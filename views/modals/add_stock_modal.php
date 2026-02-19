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

                    <!-- Warehouse -->
                    <div class="mb-3">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-control" required>
                            <option value="">Select warehouse</option>
                            <?php
                            mysqli_data_seek($warehouses, 0);
                            while ($w = mysqli_fetch_assoc($warehouses)): ?>
                                <option value="<?= $w['warehouse_id'] ?>">
                                    <?= htmlspecialchars($w['warehouse_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Product -->
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">Select product</option>
                            <?php
                            mysqli_data_seek($productsDropdown, 0);
                            while ($p = mysqli_fetch_assoc($productsDropdown)): ?>
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
