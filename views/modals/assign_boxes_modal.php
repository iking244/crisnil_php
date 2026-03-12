<div class="modal fade" id="assignBoxesModal" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content">

            <form id="assignBoxesForm">

                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="fa fa-boxes text-warning"></i>
                        Assign Boxes

                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <input type="hidden" name="delivery_item_id" id="delivery_item_id">

                    <div class="mb-3">

                        <label class="form-label">Product</label>

                        <input type="text" id="assign_product" class="form-control" readonly>

                    </div>

                    <table class="table table-bordered">

                        <thead>

                            <tr>
                                <th>Box Weight (kg)</th>
                                <th>Size</th>
                                <th>Batch</th>
                                <th>Pallet</th>
                                <th>Expiry</th>
                            </tr>

                        </thead>

                        <tbody id="boxesContainer">

                        </tbody>

                    </table>

                </div>

                <div class="modal-footer">

                    <button type="submit" class="btn btn-success w-100">

                        Save Boxes

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>