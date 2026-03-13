<div class="modal fade" id="deliveryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form class="auto-loading-form" id="deliveryForm" action="../controllers/stock_controller.php?action=save_delivery" method="POST">
                <input type="hidden" name="mode" id="delivery_mode" value="create">
                <input type="hidden" name="delivery_receipt_id" id="delivery_receipt_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliveryModalTitle">
                        <i class="fa fa-truck me-2 text-success"></i>
                        Receive Delivery
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>


                <div class="modal-body">
                    <div id="deliveryError" class="alert alert-danger d-none"></div>
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
                            <input type="text" name="dr_number"
                                class="form-control"
                                placeholder="Example: 23741"
                                required
                                autocomplete="off"
                                pattern="\d+"
                                title="Please enter a valid DR number (digits only)">

                            <!-- Load button (hidden by default) -->
                            <button type="button"
                                class="btn btn-secondary d-none"
                                id="loadDRBtn">

                                <i class="fa fa-search"></i> Load

                            </button>
                        </div>

                        <h6 class="mt-3">Delivery Items</h6>

                        <div class="items-container">
                            <table class="table table-bordered delivery-table" id="itemsTable">
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

                                    <tr class="item-row">

                                        <td>
                                            <select name="product_id[]" class="form-control" required>
                                                <option value="">Select product</option>
                                                <?php
                                                mysqli_data_seek($productsDropdown, 0);
                                                while ($p = mysqli_fetch_assoc($productsDropdown)): ?>
                                                    <option value="<?= $p['product_id'] ?>">
                                                        <?= htmlspecialchars($p['product_name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number" name="qty[]" class="form-control qty" placeholder="Boxes" required>
                                        </td>

                                        <td>
                                            <input type="text" name="unit[]" class="form-control" value="BOX" readonly>
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" name="weight[]" class="form-control weight" placeholder="kg" required>
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" name="price[]" class="form-control price" placeholder="Price/kg" required>
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" name="amount[]" class="form-control amount" readonly>
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-danger removeRow">X</button>
                                        </td>

                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-secondary" id="addRow">
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
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-save me-1"></i>
                        Save Delivery
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>