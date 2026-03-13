<?php
include "../controllers/receiving_controller.php";
?>

<div class="main">
    <div class="container-fluid">

        <h1 class="page-title mb-4">Warehouse Receiving</h1>

        <div class="row g-4">

            <!-- DELIVERY QUEUE -->

            <div class="col-lg-4">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h6 class="mb-3">
                            <i class="fa fa-list me-2"></i>
                            Receiving Queue
                        </h6>

                        <?php while ($row = mysqli_fetch_assoc($queue)): ?>

                            <div class="border rounded p-3 mb-3">

                                <strong>DR <?= $row['dr_number'] ?></strong><br>

                                <small class="text-muted">
                                    <?= $row['product_name'] ?>
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
                                    Continue
                                </button>

                            </div>

                        <?php endwhile; ?>

                    </div>
                </div>

            </div>


            <!-- BOX ENTRY -->

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h6 class="mb-3">
                            <i class="fa fa-box me-2"></i>
                            Assign Boxes
                        </h6>

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

                            <tbody>

                                <tr>
                                    <td><input class="form-control"></td>
                                    <td><input class="form-control"></td>
                                    <td><input class="form-control"></td>
                                    <td><input class="form-control"></td>
                                    <td><input type="date" class="form-control"></td>
                                </tr>

                            </tbody>

                        </table>

                        <button class="btn btn-success">
                            Save Boxes
                        </button>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>