<?php
include "../controllers/receiving_controller.php";
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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">

</head>


<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>


    <div class="main">
        <div class="container-fluid">


            <!-- PAGE HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1 class="page-title">Warehouse Receiving</h1>

                <a href="inventory_overview.php" class="btn btn-outline-dark">
                    <i class="fa fa-arrow-left me-1"></i> Back to Inventory
                </a>

            </div>



            <div class="row g-4">

                <!-- DELIVERY QUEUE -->

                <div class="col-lg-4">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6 class="mb-3">
                                <i class="fa fa-list me-2"></i>
                                Receiving Queue
                            </h6>


                            <?php if ($queue && mysqli_num_rows($queue) > 0): ?>

                                <?php while ($row = mysqli_fetch_assoc($queue)): ?>

                                    <div class="border rounded p-3 mb-3">

                                        <strong>DR <?= $row['dr_number'] ?></strong>

                                        <br>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($row['product_name']) ?>
                                        </small>

                                        <hr>

                                        <div class="d-flex justify-content-between small">
                                            <span>Boxes</span>
                                            <span><?= $row['boxes'] ?></span>
                                        </div>

                                        <div class="d-flex justify-content-between small">
                                            <span>Assigned</span>
                                            <span><?= $row['assigned'] ?></span>
                                        </div>

                                        <div class="d-flex justify-content-between small">
                                            <span>Remaining</span>
                                            <span><?= $row['boxes'] - $row['assigned'] ?></span>
                                        </div>

                                        <button class="btn btn-sm btn-primary w-100 mt-2">
                                            Continue Receiving
                                        </button>

                                    </div>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <div class="text-muted text-center py-3">
                                    No deliveries waiting for receiving.
                                </div>

                            <?php endif; ?>


                        </div>
                    </div>

                </div>



                <!-- BOX ASSIGNMENT -->

                <div class="col-lg-8">

                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h6 class="mb-3">
                                <i class="fa fa-box me-2"></i>
                                Assign Boxes
                            </h6>


                            <div class="alert alert-info">

                                Select a delivery from the left queue to start assigning boxes.

                            </div>


                            <table class="table table-bordered align-middle">

                                <thead>

                                    <tr>
                                        <th>Weight (kg)</th>
                                        <th>Size</th>
                                        <th>Batch</th>
                                        <th>Pallet</th>
                                        <th>Expiry</th>
                                    </tr>

                                </thead>


                                <tbody>

                                    <tr>

                                        <td>
                                            <input type="number" class="form-control" placeholder="0.00">
                                        </td>

                                        <td>
                                            <input class="form-control">
                                        </td>

                                        <td>
                                            <input class="form-control">
                                        </td>

                                        <td>
                                            <input class="form-control">
                                        </td>

                                        <td>
                                            <input type="date" class="form-control">
                                        </td>

                                    </tr>

                                </tbody>

                            </table>


                            <button class="btn btn-success">
                                <i class="fa fa-save me-1"></i> Save Boxes
                            </button>


                        </div>
                    </div>

                </div>


            </div>


        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../scripts/utils.js"></script>
    <script src="../scripts/table.js"></script>
    <script src="../scripts/products.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>


</body>

</html>