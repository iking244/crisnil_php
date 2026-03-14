<form class="auto-loading-form" id="assignBoxesForm"
    method="POST"
    action="../controllers/warehouse_controller.php?action=assign_boxes">

    <input type="hidden" name="delivery_item_id" id="delivery_item_id">

    <div class="modal-body">

        <label class="form-label">Product</label>

        <input type="text" id="assign_product" class="form-control" readonly>

        <table class="table table-bordered">

            <thead>

                <tr>
                    <th>Weight</th>
                    <th>Size</th>
                    <th>Batch</th>
                    <th>Pallet</th>
                    <th>Expiry</th>
                </tr>

            </thead>

            <tbody id="boxesContainer"></tbody>

        </table>

    </div>

    <button type="submit" class="btn btn-success w-100">
        Save Boxes
    </button>

</form>