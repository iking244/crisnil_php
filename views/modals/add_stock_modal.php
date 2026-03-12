<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form action="../controllers/stock_controller.php?action=add" method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-truck me-2 text-success"></i>
                        Receive Delivery
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <!-- Warehouse -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warehouse</label>
                            <select name="warehouse_id" class="form-control" required>
                                <?php
                                mysqli_data_seek($warehouses, 0);
                                while ($w = mysqli_fetch_assoc($warehouses)): ?>
                                    <option value="<?= $w['warehouse_id'] ?>">
                                        <?= htmlspecialchars($w['warehouse_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Delivery Receipt -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Receipt (DR)</label>
                            <input type="text" name="dr_number"
                                class="form-control"
                                placeholder="Example: DR-0234"
                                required>
                        </div>

                        <!-- Product -->
                        <div class="col-md-6 mb-3">
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

                        <!-- Pallet -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pallet Number</label>
                            <input type="text" name="pallet_code"
                                class="form-control"
                                placeholder="Example: P.9"
                                required>
                        </div>

                        <!-- Supplier Batch -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier Batch Code</label>
                            <input type="text" name="supplier_batch_code"
                                class="form-control"
                                placeholder="From pallet label"
                                required>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity"
                                class="form-control"
                                placeholder="Enter total units"
                                required>
                        </div>

                        <!-- Production Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Production Date</label>
                            <input type="date" name="production_date"
                                class="form-control">
                        </div>

                        <!-- Expiration Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Expiration Date</label>
                            <input type="date" name="expiration_date"
                                class="form-control" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Receipt Image (Optional)</label>
                            <input type="file"
                                name="receipt_image"
                                class="form-control"
                                accept="image/*">
                            <small class="text-muted">
                                Upload photo of delivery receipt (optional)
                            </small>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-save me-1"></i>
                        Save Delivery
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>