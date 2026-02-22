<?php
// TEMP PLACEHOLDER DATA (we replace with controller logic later)
$dispatchStats = [
    'active_trips' => 3,
    'overdue_trips' => 1,
    'unassigned_trips' => 2,
    'fleet_utilization' => 75,
    'available_trucks' => 5,
    'completed_today' => 4
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Overview - CRISNIL</title>

    <!-- Bootstrap FIRST -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Core CSS System AFTER Bootstrap -->
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/modals.css">
    <link rel="stylesheet" href="../styles/logistics/dispatch_overview.css">

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
    <h1 class="page-title">Dispatch Overview</h1>

    <div class="d-flex gap-2">
        <a href="dispatch_trips.php" class="btn btn-outline-dark">
            <i class="fa fa-road"></i> View All Trips
        </a>

        <button class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Trip
        </button>
    </div>
</div>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card blue">
            <h6>Active Trips</h6>
            <h3><?= $dispatchStats['active_trips'] ?></h3>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card red">
            <h6>Overdue Trips</h6>
            <h3><?= $dispatchStats['overdue_trips'] ?></h3>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card orange">
            <h6>Unassigned Trips</h6>
            <h3><?= $dispatchStats['unassigned_trips'] ?></h3>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card green">
            <h6>Fleet Utilization</h6>
            <h3><?= $dispatchStats['fleet_utilization'] ?>%</h3>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card gray">
            <h6>Available Trucks</h6>
            <h3><?= $dispatchStats['available_trucks'] ?></h3>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="kpi-card dark">
            <h6>Completed Today</h6>
            <h3><?= $dispatchStats['completed_today'] ?></h3>
        </div>
    </div>

</div>

<!-- MAIN CONTENT -->
<div class="row g-4">

<!-- Active Trips -->
<div class="col-lg-8">
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">Active Trips</h3>

            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Trip ID</th>
                        <th>Truck</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>ETA</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- Placeholder Rows -->
                    <tr>
                        <td><strong>#T001</strong></td>
                        <td>Mitsubishi L300 CXU 913</td>
                        <td>Kevin Santos</td>
                        <td><span class="status-badge in_transit">In Transit</span></td>
                        <td>Jun 21, 2026 14:00</td>
                    </tr>

                    <tr class="table-danger fw-semibold">
                        <td><strong>#T002</strong></td>
                        <td>Mitsubishi L300 BYK 123</td>
                        <td>Rodney Mullen</td>
                        <td>
                            <span class="status-badge delayed">Delayed</span>
                            <span class="badge bg-danger ms-1">Overdue</span>
                        </td>
                        <td>Jun 21, 2026 10:00</td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- ACTION REQUIRED -->
<div class="col-lg-4">
    <div class="card">
        <div class="card-body">

            <h3 class="mb-3">Dispatch Alerts</h3>

            <ul class="list-group list-group-flush">

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Trips Without Driver</span>
                    <span class="text-danger fw-bold">2</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Trips Missing ETA</span>
                    <span class="text-warning fw-bold">1</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Low Fleet Availability</span>
                    <span class="text-warning fw-bold">1</span>
                </li>

                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Overdue Trips</span>
                    <span class="text-danger fw-bold">
                        <?= $dispatchStats['overdue_trips'] ?>
                    </span>
                </li>

            </ul>

        </div>
    </div>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../scripts/notif.js"></script>
<script src="../scripts/sidenav.js"></script>
<script src="../scripts/dropdown2.js"></script>

</body>
</html>