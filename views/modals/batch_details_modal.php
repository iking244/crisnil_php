<div class="modal fade"
     id="batchDetailsModal"
     tabindex="-1">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa fa-barcode"></i>
                    Batch Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <!-- BODY -->
            <div class="modal-body">

                <div class="row g-3">

                    <!-- LEFT COLUMN -->
                    <div class="col-lg-8">

                        <div class="row g-3">

                            <!-- BATCH CODE -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Batch Code
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchCode">
                                    </div>

                                </div>

                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Batch Status
                                    </div>

                                    <div id="batchCondition">
                                    </div>

                                </div>

                            </div>

                            <!-- PRODUCT -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Product
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchProduct">
                                    </div>

                                </div>

                            </div>

                            <!-- PALLET -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Pallet Location
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchPallet">
                                    </div>

                                </div>

                            </div>

                            <!-- EXPIRY -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Expiry Date
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchExpiry">
                                    </div>

                                </div>

                            </div>

                            <!-- DAYS LEFT -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Shelf Life
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchShelfLife">
                                    </div>

                                </div>

                            </div>

                            <!-- QUANTITY -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Quantity
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchQuantity">
                                    </div>

                                </div>

                            </div>

                            <!-- WEIGHT -->
                            <div class="col-md-6">

                                <div class="batch-detail-card">

                                    <div class="batch-detail-label">
                                        Total Weight
                                    </div>

                                    <div class="batch-detail-value"
                                         id="batchWeight">
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-lg-4">

                        <div class="batch-detail-card h-100">

                            <div class="batch-detail-label mb-3">
                                Traceability Timeline
                            </div>

                            <div class="batch-timeline">

                                <div class="timeline-item">

                                    <div class="timeline-dot"></div>

                                    <div class="timeline-content">

                                        <div class="timeline-title">
                                            Received in Warehouse
                                        </div>

                                        <div class="timeline-date">
                                            Apr 14, 2026
                                        </div>

                                    </div>

                                </div>

                                <div class="timeline-item">

                                    <div class="timeline-dot"></div>

                                    <div class="timeline-content">

                                        <div class="timeline-title">
                                            Assigned to Pallet
                                        </div>

                                        <div class="timeline-date">
                                            Apr 14, 2026
                                        </div>

                                    </div>

                                </div>

                                <div class="timeline-item">

                                    <div class="timeline-dot"></div>

                                    <div class="timeline-content">

                                        <div class="timeline-title">
                                            Inventory Updated
                                        </div>

                                        <div class="timeline-date">
                                            Apr 15, 2026
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>