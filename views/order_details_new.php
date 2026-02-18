<?php include "../controllers/order_details_controller.php";
 ?>>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?= $order['order_id'] ?> - CRISNIL</title>
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="../styles/order_details_new.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0yZHj2xllQIw4A1IgnsEedEiKnSly640&callback=initMap"></script>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/status_helper.php'; ?>

    <main class="main-content p-4">
        <div class="container-fluid">
            <div class="title-row d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title m-0">Order Details #<?= $order['order_id'] ?></h1>
                <a href="logistics_orders.php" class="btn btn-outline-danger">
                    <i class="fas fa-arrow-left me-2"></i> Back to Job Orders
                </a>
            </div>
        </div>

            <div class="row g-4">
                <!-- Left Column: Order Summary -->
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i> Order List</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <?php if (empty($order['products'])): ?>
                                    <li class="text-muted">No products added yet.</li>
                                <?php else: ?>
                                    <?php foreach ($order['products'] as $item): ?>
                                        <li class="mb-2">
                                            <i class="fas fa-box text-danger me-2"></i>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong> x <?= $item['quantity'] ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>

                            <hr>

                            <h6><i class="fas fa-map-marker-alt text-danger"></i> Origin</h6>
                            <p class="fw-bold"><?= htmlspecialchars($order['origin']) ?></p>

                            <h6><i class="fas fa-flag-checkered text-danger"></i> Destination</h6>
                            <p class="fw-bold"><?= htmlspecialchars($order['destination']) ?></p>

                            <hr>

                            <!-- Professional Status Timeline -->
                            <h6 class="mb-3"><i class="fas fa-chart-line text-danger"></i> Status</h6>
                            <div class="status-timeline d-flex align-items-center justify-content-between position-relative">
                                <div class="text-center flex-fill <?= $order['order_status'] === 'pending' ? 'active' : '' ?>">
                                    <div class="status-circle bg-warning">1</div>
                                    <div class="status-label mt-2">Pending</div>
                                </div>
                                <div class="timeline-line"></div>

                                <div class="text-center flex-fill <?= $order['order_status'] === 'in_transit' ? 'active' : '' ?>">
                                    <div class="status-circle bg-info">2</div>
                                    <div class="status-label mt-2">In Transit</div>
                                </div>
                                <div class="timeline-line"></div>

                                <div class="text-center flex-fill <?= $order['order_status'] === 'completed' ? 'active' : '' ?>">
                                <?php if ($order['order_status'] === 'completed' && !empty($order['proof_photo'])): ?>
                                    <div class="status-circle bg-success position-relative" 
                                        style="cursor: pointer;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#proofPhotoModal"
                                        title="Click to view proof of delivery">
                                        3
                                        <!-- Small camera icon as visual cue (red badge for attention) -->
                                        <i class="fas fa-camera fa-xs position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="status-circle bg-success">3</div>
                                <?php endif; ?>
                                <div class="status-label mt-2">Completed</div>
                                </div>
                                </div>

                                <p class="text-center mt-2 fw-bold">
                                 Current Status: 
                                 <span class="badge bg-<?= 
                                 $order['order_status'] === 'pending' ? 'warning' : 
                                 ($order['order_status'] === 'in_transit' ? 'info' : 'success') 
                                 ?>">
                                <?= ucfirst($order['order_status'] ?? 'Pending') ?>
                                </span>
                            </p>
                                </div>
                                </div>
                                
                            </div>

                <!-- Right Column: Map with Real Road Path -->
                <div class="col-lg-5">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i> Map / Location</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="orderMap" style="height: 100%; min-height: 486px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Driver Details -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Driver Details</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($order['trip_id'])): ?>
                                <div class="alert alert-warning text-center p-4">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                    <h5>Not yet dispatched</h5>
                                    <p>Assign a driver and truck for this order.</p>
                                    <a href="trips.php" class="btn btn-warning">Go to Trips</a>
                                </div>
                            <?php else: ?>
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <div class="truck-placeholder rounded-circle d-flex align-items-center justify-content-center shadow" style="width:120px;height:120px;margin:auto;background:#e3f2fd;">
                                            <i class="fas fa-truck fa-3x text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <h5 class="mb-1"><?= htmlspecialchars($order['driver_name'] ?? 'Driver Name') ?></h5>
                                        <p class="lead mb-2">
                                            Truck: <strong><?= htmlspecialchars($order['truck_plate'] ?? 'N/A') ?></strong> 
                                            (<?= htmlspecialchars($order['truck_model'] ?? 'N/A') ?>)
                                        </p>
                                        <p class="mb-0">
                                            Trip Status: 
                                            <span class="badge bg-<?= $order['trip_status'] === 'in_transit' ? 'info' : 'success' ?> fs-6">
                                                <?= ucfirst($order['trip_status'] ?? 'Pending') ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- Proof Photo Modal -->
<div class="modal fade" id="proofPhotoModal" tabindex="-1" aria-labelledby="proofPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofPhotoModalLabel">
                    Proof of Delivery – Order #<?= htmlspecialchars($order['order_id'] ?? 'N/A') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                
                <?php if (!empty($order['proof_photo'])): ?>
                    <img src="../uploads/job_proofs/<?= htmlspecialchars($order['proof_photo']) ?>" 
                         class="img-fluid rounded shadow" 
                         style="max-height: 70vh; object-fit: contain;" 
                         alt="Proof of Delivery">
                <?php else: ?>
                    <p class="text-muted py-5">No proof photo available for this order.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


</body>
<script>
    window.orderData = <?= json_encode($order ?? []) ?>;
</script>
<!-- Your custom map logic -->
<script src="../scripts/order_details_new.js"></script>
<!-- Other scripts (bootstrap, notif.js, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/notif.js"></script>
</html>