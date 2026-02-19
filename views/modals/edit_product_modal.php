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