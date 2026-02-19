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

                                

