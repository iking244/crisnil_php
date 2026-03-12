<div class="modal fade" id="addStockModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
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
                            <select name="warehouse_id" class="form-control" disabled>
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

                        <h6 class="mt-3">Delivery Items</h6>
                        <div class="items-container">
                            <table class="table table-bordered delivery-table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Total Weight</th>
                                        <th>Price per Weight</th>
                                        <th>Total Amount</th>
                                        <th></th>
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
                                            <input type="text" name="pallet_code[]" class="form-control" placeholder="P.9">
                                        </td>

                                        <td>
                                            <input type="text" name="batch_code[]" class="form-control" placeholder="From label">
                                        </td>

                                        <td>
                                            <input type="number" name="quantity[]" class="form-control">
                                        </td>

                                        <td>
                                            <input type="date" name="expiration_date[]" class="form-control">
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