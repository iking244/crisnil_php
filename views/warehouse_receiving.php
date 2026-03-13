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

    <style>
        .receiving-card {
            border-radius: 10px;
            border: 1px solid #eee;
            transition: .2s;
        }

        .receiving-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, .05);
        }

        .progress {
            height: 6px;
        }

        .metric {
            font-size: 13px;
            color: #666;
        }

        .metric strong {
            font-size: 15px;
        }
    </style>

</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <div class="main">

        <div class="container-fluid">

            <!-- HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="page-title">Warehouse Receiving</h1>

                <a href="products_overview.php" class="btn btn-outline-dark">
                    <i class="fa fa-arrow-left me-1"></i> Back to Inventory
                </a>

            </div>


            <!-- RECEIVING GRID -->

            <div class="row g-4">

                <?php while ($row = mysqli_fetch_assoc($receivingItems)): ?>

                    <?php

                    $progress = 0;

                    if ($row['expected_boxes'] > 0) {
                        $progress = ($row['received_boxes'] / $row['expected_boxes']) * 100;
                    }

                    ?>

                    <div class="col-lg-4">

                        <div class="card receiving-card h-100">

                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-start mb-2">

                                    <div>

                                        <h6 class="fw-bold mb-1">
                                            DR <?= $row['dr_number'] ?>
                                        </h6>

                                        <div class="text-muted small">
                                            <?= htmlspecialchars($row['product_name']) ?>
                                        </div>

                                    </div>

                                    <button class="btn btn-primary btn-sm assignBtn"
                                        data-id="<?= $row['delivery_item_id'] ?>"
                                        data-product="<?= htmlspecialchars($row['product_name']) ?>"
                                        data-qty="<?= $row['remaining_boxes'] ?>">

                                        <i class="fa fa-box"></i> Assign

                                    </button>

                                </div>

                                <div class="small text-muted mb-2">

                                    Expected Boxes: <?= $row['expected_boxes'] ?>

                                </div>

                                <div class="progress mb-3">

                                    <div class="progress-bar bg-success"
                                        style="width: <?= $progress ?>%">

                                    </div>

                                </div>

                                <div class="row text-center">

                                    <div class="col">

                                        <div class="metric">
                                            Boxes
                                            <br>
                                            <strong><?= $row['expected_boxes'] ?></strong>
                                        </div>

                                    </div>

                                    <div class="col">

                                        <div class="metric">
                                            Assigned
                                            <br>
                                            <strong><?= $row['received_boxes'] ?></strong>
                                        </div>

                                    </div>

                                    <div class="col">

                                        <div class="metric">
                                            Remaining
                                            <br>
                                            <strong><?= $row['remaining_boxes'] ?></strong>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>

    </div>


    <?php include 'modals/assign_boxes_modal.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../scripts/utils.js"></script>
    <script src="../scripts/assign_boxes.js"></script>
    <script src="../scripts/sidenav.js"></script>

</body>

</html>