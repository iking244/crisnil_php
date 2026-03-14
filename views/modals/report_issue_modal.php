<div class="modal fade" id="reportIssueModal" tabindex="-1">

    <div class="modal-dialog modal-md">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa fa-exclamation-triangle text-danger"></i>
                    Report Delivery Issue
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="reportIssueForm">

                    <input type="hidden" id="issue_delivery_item_id" name="delivery_item_id">

                    <div class="mb-3">

                        <label class="form-label">Product</label>

                        <input type="text" id="issue_product_name"
                            class="form-control"
                            readonly>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">Issue Type</label>

                        <select class="form-control" name="issue_type" required>

                            <option value="">Select Issue</option>
                            <option value="missing">Missing Box</option>
                            <option value="damaged">Damaged Box</option>

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">Quantity</label>

                        <input type="number"
                            class="form-control"
                            name="qty"
                            min="1"
                            value="1"
                            required>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">Notes (optional)</label>

                        <textarea
                            class="form-control"
                            name="notes"
                            rows="3"></textarea>

                    </div>

                </form>

            </div>


            <div class="modal-footer">

                <button class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cancel

                </button>

                <button class="btn btn-danger"
                    id="submitIssueBtn">

                    <i class="fa fa-exclamation-triangle"></i>
                    Submit Issue

                </button>

            </div>

        </div>

    </div>

</div>