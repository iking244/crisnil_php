<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

include "../../config/database_conn.php";

/* =========================
   PAGINATION SETTINGS
========================= */

$per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $per_page;

/* =========================
   TOTAL COUNT
========================= */

$total_sql = "SELECT COUNT(*) as total FROM tbl_system_logs";
$total_res = mysqli_query($databaseconn, $total_sql);
$total_row = mysqli_fetch_assoc($total_res);
$total_logs = $total_row['total'];
$total_pages = ceil($total_logs / $per_page);

/* =========================
   FETCH PAGINATED LOGS
========================= */

$sql = "
    SELECT 
        l.log_id,
        l.created_at,
        l.user_id,
        COALESCE(u.USER_NAME, 'System/Guest') AS username,
        l.action,
        l.description
    FROM tbl_system_logs l
    LEFT JOIN crisnil_users u 
        ON l.user_id = u.USER_ID
    ORDER BY l.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$result = mysqli_query($databaseconn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Logs - CRISNIL</title>

    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/products/products.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Action Logs</h1>
        <a href="dashboard.php" class="btn btn-outline-dark">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- KPI -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="kpi-card blue d-flex justify-content-between align-items-center">
                <div>
                    <h6>Total Logs</h6>
                    <h3><?= number_format($total_logs) ?></h3>
                </div>
                <i class="fa fa-history fa-2x text-white opacity-75"></i>
            </div>
        </div>

        <div class="col-md-6">
            <div class="kpi-card gray d-flex justify-content-between align-items-center">
                <div>
                    <h6>Page</h6>
                    <h3><?= $page ?> / <?= $total_pages ?></h3>
                </div>
                <i class="fa fa-layer-group fa-2x text-white opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-body">

            <h6 class="mb-3">Recent Activity</h6>

            <?php if ($total_logs > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $action = strtolower($row['action']);
                            $badge = 'bg-primary';

                            if (strpos($action, 'failed') !== false) $badge = 'bg-danger';
                            elseif (strpos($action, 'stock_in') !== false) $badge = 'bg-success';
                            elseif (strpos($action, 'stock_out') !== false) $badge = 'bg-warning';
                            elseif (strpos($action, 'create') !== false) $badge = 'bg-info';
                            elseif (strpos($action, 'update') !== false) $badge = 'bg-secondary';
                            ?>

                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= strtoupper($row['action']) ?></span></td>
                                <td><?= htmlspecialchars($row['description']) ?: '—' ?></td>
                            </tr>

                        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <nav class="mt-3">
                    <ul class="pagination justify-content-end">

                        <!-- PREVIOUS -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                        </li>

                        <!-- PAGE NUMBERS -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- NEXT -->
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                        </li>

                    </ul>
                </nav>

            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <p>No activity recorded.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php mysqli_free_result($result); ?>