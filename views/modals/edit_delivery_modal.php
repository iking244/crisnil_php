<div class="modal fade" id="editDeliveryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form class="auto-loading-form"
                id="editDeliveryForm"
                action="../controllers/stock_controller.php?action=update_delivery"
                method="POST">

                <!-- Hidden fields -->
                <input type="hidden" name="delivery_receipt_id" id="edit_delivery_receipt_id">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-edit me-2 text-warning"></i>
                        Edit Delivery Receipt
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div id="editDeliveryError" class="alert alert-danger d-none"></div>

                    <div class="row">

                        <!-- Warehouse -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">Warehouse</label>

                            <select class="form-control" disabled>

                                <?php
                                mysqli_data_seek($warehouses, 0);
                                while ($w = mysqli_fetch_assoc($warehouses)): ?>

                                    <option value="<?= $w['warehouse_id'] ?>">
                                        <?= htmlspecialchars($w['warehouse_name']) ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                            <input type="hidden" name="warehouse_id" value="1">

                        </div>

                        <!-- Delivery Receipt -->
                        <div class="col-md-6 mb-3">

                            <label class="form-label">Delivery Receipt (DR)</label>

                            <div class="input-group">

                                <input type="text"
                                    name="dr_number"
                                    id="edit_dr_number"
                                    class="form-control"
                                    placeholder="Example: 23741"
                                    required
                                    autocomplete="off"
                                    pattern="\d+"
                                    title="Please enter a valid DR number (digits only)">

                                <button type="button"
                                    class="btn btn-secondary"
                                    id="loadDRBtn">

                                    <i class="fa fa-search"></i> Load

                                </button>

                            </div>

                        </div>

                        <h6 class="mt-3">Delivery Items</h6>

                        <div class="items-container">

                            <table class="table table-bordered delivery-table"
                                id="editItemsTable">

                                <thead>
                                    <tr>
                                        <th style="width:25%">Product</th>
                                        <th style="width:10%">Qty</th>
                                        <th style="width:10%">Unit</th>
                                        <th style="width:15%">Total Weight (kg)</th>
                                        <th style="width:15%">Price / Kg</th>
                                        <th style="width:15%">Total Amount</th>
                                        <th style="width:5%"></th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <!-- Rows will be inserted via JS -->

                                </tbody>

                            </table>

                        </div>

                        <button type="button"
                            class="btn btn-secondary"
                            id="editAddRow">

                            + Add Item

                        </button>

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

                    <button type="submit"
                        class="btn btn-warning w-100"
                        id="updateDeliveryBtn">

                        <i class="fa fa-save me-1"></i>
                        Update Delivery

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>