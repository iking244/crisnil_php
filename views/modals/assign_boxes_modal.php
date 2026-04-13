<div class="modal fade" id="assignBoxesModal" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content">

            <form class="auto-loading-form"
                id="assignBoxesForm"
                method="POST"
                action="../controllers/warehouse_controller.php?action=assign_boxes">

                <!-- hidden fields -->
                <input type="hidden" name="delivery_item_id" id="delivery_item_id">
                <input type="hidden" name="pallet_id" id="pallet_id">

                <div class="modal-header">

                    <h5 class="modal-title">
                        <i class="fa fa-boxes text-warning me-2"></i>
                        Encode Boxes
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="assign_product" class="form-control" readonly>
                    </div>

                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th>Weight (kg)</th>
                                <th>Size</th>
                                <th>Batch</th>
                                <th>Pallet</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>

                        <tbody id="boxesContainer">
                            <!-- JS injects rows here -->
                        </tbody>

                    </table>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-1"></i>
                        Save Boxes
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>