<?php
include "../controllers/logistics_orders_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Orders Overview - CRISNIL</title>

    <!-- Bootstrap FIRST -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Core CSS System AFTER Bootstrap -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/modals.css">
    <link rel="stylesheet" href="../styles/logistics/orders_overview.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>

    <div class="main">
        <div class="container-fluid">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Job Orders Overview</h1>



                <div class="d-flex gap-2">
                    <a href="logistics_orders.php" class="btn btn-outline-dark">
                        <i class="fa fa-list"></i> View All Jobs
                    </a>
                    <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#createOrderModal">
                        <i class="fa fa-plus"></i> Create Job Order
                    </button>


                </div>
            </div>

            <!-- KPI CARDS (Operational Focus) -->
            <div class="row g-3 mb-4">

                <div class="col-6 col-md-4 col-lg-2">
                    <a href="logistics_orders.php?filter=pending" class="text-decoration-none text-dark">
                        <div class="kpi-card orange">
                            <h6>Pending</h6>
                            <h3><?= $overviewStats['pending'] ?? 0 ?></h3>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <a href="logistics_orders.php?filter=assigned" class="text-decoration-none text-dark">
                        <div class="kpi-card blue">
                            <h6>Assigned</h6>
                            <h3><?= $overviewStats['assigned'] ?? 0 ?></h3>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <a href="logistics_orders.php?filter=in_transit" class="text-decoration-none text-dark">
                        <div class="kpi-card green">
                            <h6>In Transit</h6>
                            <h3><?= $overviewStats['in_transit'] ?? 0 ?></h3>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <a href="logistics_orders.php?filter=overdue" class="text-decoration-none text-dark">
                        <div class="kpi-card red">
                            <h6>Overdue</h6>
                            <h3><?= $overviewStats['overdue'] ?? 0 ?></h3>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <a href="logistics_orders.php?filter=blocked" class="text-decoration-none text-dark">
                        <div class="kpi-card gray">
                            <h6>Blocked</h6>
                            <h3><?= $overviewStats['blocked'] ?? 0 ?></h3>
                        </div>
                    </a>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="kpi-card dark">
                        <h6>Completed Today</h6>
                        <h3><?= $overviewStats['completed_today'] ?? 0 ?></h3>
                    </div>
                </div>

            </div>

            <!-- MAIN CONTENT -->
            <div class="row g-4">

                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="mb-0">Recent Job Orders</h3>
                            </div>

                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Origin</th>
                                        <th>Destination</th>
                                        <th>Status</th>
                                        <th>ETA</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if ($overviewRecent && mysqli_num_rows($overviewRecent) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($overviewRecent)): ?>

                                            <?php
                                            $isOverdue = false;
                                            if (
                                                !empty($row['eta']) &&
                                                $row['status'] !== 'completed' &&
                                                strtotime($row['eta']) < time()
                                            ) {
                                                $isOverdue = true;
                                            }
                                            ?>

                                            <tr class="<?= $isOverdue ? 'table-danger fw-semibold' : '' ?>">
                                                <td><strong>#<?= $row['id'] ?></strong></td>
                                                <td><?= htmlspecialchars($row['origin']) ?></td>
                                                <td><?= htmlspecialchars($row['destination']) ?></td>

                                                <td>
                                                    <span class="status-badge <?= $row['status'] ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                                                    </span>
                                                    <?php if ($isOverdue): ?>
                                                        <span class="badge bg-danger ms-1">Overdue</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?= !empty($row['eta'])
                                                        ? date('M d, Y H:i', strtotime($row['eta']))
                                                        : '<span class="text-muted">Not Set</span>' ?>
                                                </td>

                                                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                            </tr>

                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No recent job orders.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <!-- ACTION REQUIRED -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">

                            <h3 class="mb-3">Action Required</h3>

                            <ul class="list-group list-group-flush">

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Overdue Orders</span>
                                    <span class="<?= isset($overviewStats['overdue']) && $overviewStats['overdue'] > 0 ? 'text-danger fw-bold' : '' ?>">
                                        <?= $overviewStats['overdue'] ?? 0 ?>
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Missing ETA</span>
                                    <span class="<?= $missingEta > 0 ? 'text-warning fw-bold' : '' ?>">
                                        <?= $missingEta ?>
                                    </span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Pending Assignment</span>
                                    <span class="<?= $pending > 0 ? 'text-warning fw-bold' : '' ?>">
                                        <?= $pending ?>
                                    </span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Blocked Orders</span>
                                    <span class="<?= $blocked > 0 ? 'text-danger fw-bold' : '' ?>">
                                        <?= $blocked ?>
                                    </span>
                                </li>

                            </ul>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- KEEP YOUR EXISTING MODAL AND SCRIPTS BELOW UNCHANGED -->
    <!-- ================= SCRIPTS ================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>
    <script>
        window.productList = [
            <?php
            mysqli_data_seek($products, 0);
            while ($p = $products->fetch_assoc()):
            ?> {
                    id: <?= $p['product_id'] ?>,
                    name: "<?= addslashes($p['product_name']) ?>",
                    unit: "<?= $p['unit'] ?>"
                },
            <?php endwhile; ?>
        ];
    </script>
    <script>
        document.getElementById('createOrderModal')
            .addEventListener('shown.bs.modal', function() {

                const tbody = document.querySelector('#createItemsTable tbody');

                if (tbody.children.length === 0) {
                    addItemRow('createItemsTable');
                }
            });
    </script>

</body>

</html>