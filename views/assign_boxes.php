<?php
include "../controllers/warehouse_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Boxes - CRISNIL</title>

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

        <div class="container-fluid pt-2">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h1>Assign Boxes</h1>

            </div>

            <div class="card">

                <div class="card-body">

                    <table class="table table-bordered">

                        <thead>
                            <tr>
                                <th>DR</th>
                                <th>Product</th>
                                <th>Boxes</th>
                                <th>Total Weight</th>
                                <th>Assigned</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php while ($row = mysqli_fetch_assoc($deliveryItems)): ?>

                                <tr>

                                    <td><?= $row['dr_number'] ?></td>

                                    <td><?= htmlspecialchars($row['product_name']) ?></td>

                                    <td><?= $row['qty'] ?></td>

                                    <td><?= $row['total_weight'] ?> kg</td>

                                    <td><?= $row['assigned_boxes'] ?></td>

                                    <td>

                                        <button class="btn btn-primary btn-sm assignBtn"
                                            data-id="<?= $row['delivery_item_id'] ?>"
                                            data-product="<?= htmlspecialchars($row['product_name']) ?>"
                                            data-qty="<?= $row['qty'] ?>">

                                            <i class="fa fa-box"></i> Assign

                                        </button>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>
            </div>

        </div>
    </div>

    <?php include 'modals/assign_boxes_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/assign_boxes.js"></script>

</body>

</html>