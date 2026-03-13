<?php
include "../controllers/warehouse_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Receiving - CRISNIL</title>

    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/products/products.css">
    <link rel="stylesheet" href="../styles/modals.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <div class="main">
        <div class="container-fluid pt-3">


            <!-- PAGE HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="page-title">Warehouse Receiving</h1>

                <a href="inventory_overview.php" class="btn btn-outline-dark">
                    <i class="fa fa-arrow-left me-1"></i> Back to Inventory
                </a>

            </div>


            <?php

            $totalItems = 0;
            $totalBoxes = 0;
            $totalAssigned = 0;

            $rows = [];

            while ($row = mysqli_fetch_assoc($deliveryItems)) {

                $rows[] = $row;

                $totalItems++;
                $totalBoxes += $row['qty'];
                $totalAssigned += $row['assigned_boxes'];
            }

            $remaining = $totalBoxes - $totalAssigned;

            ?>


            <!-- KPI SUMMARY -->

            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="metric-card">
                        <h6>Delivery Items</h6>
                        <h4><?= $totalItems ?></h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card">
                        <h6>Total Boxes</h6>
                        <h4><?= $totalBoxes ?></h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card">
                        <h6>Assigned</h6>
                        <h4><?= $totalAssigned ?></h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card">
                        <h6>Remaining</h6>
                        <h4><?= $remaining ?></h4>
                    </div>
                </div>

            </div>


            <!-- RECEIVING QUEUE -->

            <div class="row g-3">

                <?php foreach ($rows as $row):

                    $remainingBoxes = $row['qty'] - $row['assigned_boxes'];
                    $progress = $row['qty'] > 0 ? ($row['assigned_boxes'] / $row['qty']) * 100 : 0;

                ?>

                    <div class="col-lg-4">

                        <div class="card receiving-card shadow-sm h-100">

                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-start mb-2">

                                    <div>

                                        <strong>DR <?= $row['dr_number'] ?></strong>

                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($row['product_name']) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= $row['total_weight'] ?> kg
                                        </small>

                                    </div>

                                    <button class="btn btn-primary btn-sm assignBtn"
                                        data-id="<?= $row['delivery_item_id'] ?>"
                                        data-product="<?= htmlspecialchars($row['product_name']) ?>"
                                        data-qty="<?= $row['qty'] ?>">

                                        <i class="fa fa-box"></i> Assign

                                    </button>

                                </div>


                                <div class="progress mb-3" style="height:6px;">

                                    <div class="progress-bar bg-success"
                                        style="width: <?= $progress ?>%">
                                    </div>

                                </div>


                                <div class="small">

                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Boxes</span>
                                        <span><?= $row['qty'] ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Assigned</span>
                                        <span><?= $row['assigned_boxes'] ?></span>
                                    </div>

                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Remaining</span>
                                        <span class="fw-semibold"><?= $remainingBoxes ?></span>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


        </div>
    </div>


    <!-- ASSIGN BOXES MODAL -->
    <?php include 'modals/assign_boxes_modal.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../scripts/utils.js"></script>
    <script src="../scripts/assign_boxes.js"></script>

</body>

</html>