<div class="modal fade" id="viewPalletModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <!-- Header -->

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa fa-layer-group me-2"></i>
                    Pallet <span id="palletCodeTitle"></span>
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>


            <!-- Body -->

            <div class="modal-body">

                <!-- Pallet Summary -->

                <div class="row mb-3 text-center">

                    <div class="col">

                        <div class="metric">
                            Boxes
                            <br>
                            <strong id="palletBoxCount">0</strong>
                        </div>

                    </div>

                    <div class="col">

                        <div class="metric">
                            Total Weight
                            <br>
                            <strong><span id="palletTotalWeight">0</span> kg</strong>
                        </div>

                    </div>

                </div>


                <!-- Table -->

                <div class="table-responsive">

                    <table class="table table-sm align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>Product</th>
                                <th>Weight (kg)</th>
                                <th>Batch</th>
                                <th>Expiry</th>
                            </tr>

                        </thead>

                        <tbody id="palletBoxesContainer">

                            <!-- Filled dynamically by JS -->

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>