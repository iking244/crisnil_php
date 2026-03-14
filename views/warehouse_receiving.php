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
    <link rel="stylesheet" href="../styles/warehouse/warehouse_receiving.css">
    <link rel="stylesheet" href="../styles/modals.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <div class="main">

        <div class="container-fluid">

            <!-- PAGE HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="page-title">Warehouse Receiving</h1>

                <a href="product_overview.php" class="btn btn-outline-dark">
                    <i class="fa fa-arrow-left me-1"></i> Back to Inventory
                </a>

            </div>


            <!-- KPI DASHBOARD -->

            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="kpi-card blue">

                        <h6>Pending Delivery Items</h6>
                        <h3><?= $pendingItems ?></h3>
                        <div class="kpi-sub">items waiting encoding</div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="kpi-card orange">

                        <h6>Boxes Pending</h6>
                        <h3><?= $boxesPending ?></h3>
                        <div class="kpi-sub">boxes not yet received</div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="kpi-card green">

                        <h6>Active Pallets</h6>
                        <h3><?= $activePallets ?></h3>
                        <div class="kpi-sub">currently in use</div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="kpi-card gray">

                        <h6>Completed Today</h6>
                        <h3><?= $receivedToday ?></h3>
                        <div class="kpi-sub">fully received DRs</div>

                    </div>

                </div>

            </div>


            <!-- PALLET DASHBOARD -->

            <div class="card mb-4">

                <div class="card-header">
                    <h3>Active Pallets</h3>
                </div>

                <div class="row g-3">

                    <?php while ($p = mysqli_fetch_assoc($palletCapacity)): ?>

                        <?php
                        $percent = $p['percent_used'];

                        if ($percent > 80) {
                            $color = "#dc3545";
                        } elseif ($percent > 40) {
                            $color = "#ffc107";
                        } else {
                            $color = "#28a745";
                        }
                        ?>

                        <div class="col-md-2">

                            <div class="card pallet-card text-center p-3"
                                style="--fill: <?= $percent ?>%; --color: <?= $color ?>;">

                                <div class="pallet-card-content">

                                    <strong><?= $p['pallet_code'] ?></strong>

                                    <div class="small text-muted mt-1">
                                        <?= $p['box_count'] ?> / 60 boxes
                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </div>

            <!-- RECEIVING ITEMS -->

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

                                <!-- HEADER -->

                                <div class="d-flex justify-content-between align-items-start mb-2">

                                    <div>

                                        <h6 class="fw-bold mb-1">
                                            DR <?= $row['dr_number'] ?>
                                        </h6>

                                        <div class="text-muted small">
                                            <?= htmlspecialchars($row['product_name']) ?>
                                        </div>

                                    </div>

                                </div>


                                <!-- EXPECTED -->

                                <div class="small text-muted mb-2">
                                    Expected Boxes: <?= $row['expected_boxes'] ?>
                                </div>


                                <!-- PROGRESS -->

                                <div class="progress mb-3">

                                    <div class="progress-bar bg-success"
                                        style="width: <?= $progress ?>%">
                                    </div>

                                </div>


                                <!-- METRICS -->

                                <div class="row text-center mb-3">

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


                                <!-- PALLET SELECT -->

                                <div class="mb-3">

                                    <label class="small text-muted mb-1">Assign Pallet</label>

                                    <select class="form-control palletSelect">

                                        <option value="">Select Pallet</option>

                                        <?php
                                        mysqli_data_seek($pallets, 0);
                                        while ($p = mysqli_fetch_assoc($pallets)):
                                        ?>

                                            <option value="<?= $p['pallet_id'] ?>">
                                                <?= $p['pallet_code'] ?>
                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>


                                <!-- ACTION BUTTONS -->

                                <div class="row g-2">

                                    <div class="col-6">

                                        <button class="btn btn-primary btn-sm assignBtn w-100"
                                            data-id="<?= $row['delivery_item_id'] ?>"
                                            data-product="<?= htmlspecialchars($row['product_name']) ?>"
                                            data-qty="<?= $row['remaining_boxes'] ?>">

                                            <i class="fa fa-box"></i> Encode

                                        </button>

                                    </div>

                                    <div class="col-6">

                                        <button class="btn btn-outline-danger btn-sm reportIssueBtn w-100"
                                            data-id="<?= $row['delivery_item_id'] ?>"
                                            data-product="<?= htmlspecialchars($row['product_name']) ?>">

                                            <i class="fa fa-exclamation-triangle"></i> Issue

                                        </button>

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
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/assign_boxes.js"></script>


    <script>
        /* PASS PALLET ID TO MODAL */

        document.querySelectorAll(".assignBtn").forEach(btn => {

            btn.addEventListener("click", function() {

                let palletSelect = this.closest(".card-body").querySelector(".palletSelect");

                let palletId = palletSelect.value;

                if (!palletId) {

                    alert("Please select a pallet first");

                    return;

                }

                document.getElementById("pallet_id").value = palletId;

            });

        });
    </script>

</body>

</html>