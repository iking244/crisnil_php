<?php
// views/action_logs.php

session_start();

// Basic login check (same as other views)
if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

include "../config/database_conn.php";

// Fetch logs - newest first, limit to recent ones
$sql = "
    SELECT 
        al.id,
        al.created_at,
        al.user_id,
        COALESCE(u.USER_NAME, 'System/Guest') AS username,
        al.action,
        al.description,
        al.ip_address
    FROM action_logs al
    LEFT JOIN crisnil_users u ON al.user_id = u.USER_ID
    ORDER BY al.created_at DESC 
    LIMIT $offset, $per_page
";

$result = mysqli_query($databaseconn, $sql);

$total_sql = "SELECT COUNT(*) as total FROM action_logs";
$total_res = mysqli_query($databaseconn, $total_sql);
$total_row = mysqli_fetch_assoc($total_res);
$total_rows = $total_row['total'];
$total_pages = ceil($total_rows / $per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Logs - CRISNIL</title>

    <!-- Same styles as product_overview -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <!-- products.css has many of the card/table styles – reuse it -->
    <link rel="stylesheet" href="../styles/products/products.css">

    <!-- External libs – identical to product_overview -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Shared Crisnil extras -->
    <link rel="stylesheet" href="../styles/floatingBtn.css">
    <link rel="stylesheet" href="../styles/modals.css">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">
    <div class="container-fluid">

        <!-- Page Header – same layout as Products Overview -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Action Logs</h1>

            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-dark">
                    <i class="fa fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Status Alert – similar to inventory healthy/low stock alert -->
        <?php if ($total_logs === 0): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-1"></i>
                No actions have been logged yet.
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle me-1"></i>
                System activity is being tracked. Showing recent actions.
            </div>
        <?php endif; ?>

        <!-- KPI Cards – simplified to 2 for now, same card styles -->
        <div class="row mb-4 g-3">

            <div class="col-md-6">
                <div class="kpi-card blue d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Total Logged Actions</h6>
                        <h3><?= number_format($total_logs) ?></h3>
                    </div>
                    <i class="fa fa-history fa-2x text-white opacity-75"></i>
                </div>
            </div>

            <div class="col-md-6">
                <div class="kpi-card gray d-flex justify-content-between align-items-center">
                    <div>
                        <h6>Showing Recent</h6>
                        <h3>Up to 100 entries</h3>
                    </div>
                    <i class="fa fa-clock fa-2x text-white opacity-75"></i>
                </div>
            </div>

        </div>

        <!-- Main Content – Card with table, same as Recent Stock Activity but full-width -->
        <div class="card h-100">
            <div class="card-body">

                <h6 class="mb-3">Recent Activity Log</h6>

                <?php if ($total_logs > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['username']) ?>
                                            <?php if ($row['user_id'] === null): ?>
                                                <small class="text-muted">(System/Guest)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                    if (strpos($row['action'], 'failed') !== false) echo 'bg-danger';
                                                    elseif ($row['action'] === 'login') echo 'bg-success';
                                                    elseif ($row['action'] === 'logout') echo 'bg-secondary';
                                                    else echo 'bg-primary';
                                                ?>">
                                                <?= htmlspecialchars($row['action']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['description']) ?: '—' ?></td>
                                        <td class="text-muted"><?= htmlspecialchars($row['ip_address']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <i class="fa fa-history fa-3x mb-3 opacity-50"></i>
                        <p>No activity recorded.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- Scripts – same as product_overview -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/table.js"></script>
<script src="../scripts/notif.js"></script>
<script src="../scripts/sidenav.js"></script>
<script src="../scripts/dropdown2.js"></script>

</body>
</html>

<?php
mysqli_free_result($result);
?>