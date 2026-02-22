<?php
include "../controllers/trips_controller.php";
// TEMP PLACEHOLDER DATA (replace with controller later)
$dispatchStats = [
    // Workload
    'waiting_jobs' => 4,
    'trips_today' => 5,
    'active_trips' => 6,
    'overdue_trips' => 1,

    // Capacity
    'drivers_available' => 2,
    'drivers_assigned' => 3,
    'trucks_available' => 7,
    'trucks_in_use' => 5,
    'fleet_utilization' => 50
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

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="page-title">Dispatch Overview</h1>

                <div class="d-flex gap-2">
                    <a href="trips_management.php" class="btn btn-outline-dark">
                        <i class="fa fa-road"></i> View All Trips
                    </a>

                    <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#dispatchActionModal">
                        <i class="fa fa-cogs"></i> Dispatch Actions
                    </button>
                </div>
            </div>

            <!-- ===================== -->
            <!-- WORKLOAD SECTION -->
            <!-- ===================== -->

            <h5 class="mb-3">Dispatch Workload</h5>

            <div class="row g-3 mb-4">

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card orange">
                        <h6>Jobs Waiting</h6>
                        <h3><?= $dispatchStats['waiting_jobs'] ?></h3>
                        <small class="text-muted">Pending grouping</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card blue">
                        <h6>Trips Created Today</h6>
                        <h3><?= $dispatchStats['trips_today'] ?></h3>
                        <small class="text-muted">Auto & manual</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card dark">
                        <h6>Active Trips</h6>
                        <h3><?= $dispatchStats['active_trips'] ?></h3>
                        <small class="text-muted">Currently in progress</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card red">
                        <h6>Overdue Trips</h6>
                        <h3><?= $dispatchStats['overdue_trips'] ?></h3>
                        <small class="text-muted">Requires attention</small>
                    </div>
                </div>

            </div>

            <!-- ===================== -->
            <!-- FLEET CAPACITY SECTION -->
            <!-- ===================== -->

            <h5 class="mb-3">Fleet Capacity</h5>

            <div class="row g-3 mb-4">

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card green">
                        <h6>Drivers Available</h6>
                        <h3><?= $dispatchStats['drivers_available'] ?></h3>
                        <small class="text-muted">Ready for assignment</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card gray">
                        <h6>Drivers Assigned</h6>
                        <h3><?= $dispatchStats['drivers_assigned'] ?></h3>
                        <small class="text-muted">On active trips</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card blue">
                        <h6>Trucks Available</h6>
                        <h3><?= $dispatchStats['trucks_available'] ?></h3>
                        <small class="text-muted">Ready for dispatch</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-3">
                    <div class="kpi-card dark">
                        <h6>Fleet Utilization</h6>
                        <h3><?= $dispatchStats['fleet_utilization'] ?>%</h3>
                        <small class="text-muted">Trucks in use</small>
                    </div>
                </div>

            </div>

            <!-- ===================== -->
            <!-- MAIN CONTENT -->
            <!-- ===================== -->

            <div class="row g-4">

                <!-- Recent Dispatch Trips -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">

                            <h3 class="mb-3">Recent Dispatch Trips</h3>

                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Trip ID</th>
                                        <th>Stops</th>
                                        <th>Truck</th>
                                        <th>Driver</th>
                                        <th>Status</th>
                                        <th>ETA</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td><strong>#T001</strong></td>
                                        <td>3</td>
                                        <td>Mitsubishi L300 CXU 913</td>
                                        <td>Kevin Santos</td>
                                        <td>
                                            <span class="status-badge in_transit">
                                                In Transit
                                            </span>
                                        </td>
                                        <td>Jun 21, 2026 14:00</td>
                                    </tr>

                                    <tr class="table-danger fw-semibold">
                                        <td><strong>#T002</strong></td>
                                        <td>2</td>
                                        <td>Mitsubishi L300 BYK 123</td>
                                        <td>Rodney Mullen</td>
                                        <td>
                                            <span class="status-badge delayed">
                                                Delayed
                                            </span>
                                            <span class="badge bg-danger ms-1">
                                                Overdue
                                            </span>
                                        </td>
                                        <td>Jun 21, 2026 10:00</td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                <!-- Dispatch Alerts -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">

                            <h3 class="mb-3">Dispatch Alerts</h3>

                            <ul class="list-group list-group-flush">

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Jobs Waiting for Grouping</span>
                                    <span class="text-warning fw-bold">
                                        <?= $dispatchStats['waiting_jobs'] ?>
                                    </span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Trips Without Driver</span>
                                    <span class="text-danger fw-bold">1</span>
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

    <!-- =========================
     DISPATCH ACTION CENTER
========================= -->

    <div class="modal fade" id="dispatchActionModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Dispatch Action Center</h5>
                    <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- NAV TABS -->
                    <ul class="nav nav-tabs mb-4" id="dispatchTab" role="tablist">

                        <li class="nav-item">
                            <button class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#createTab">
                                Create Trip
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#editTab">
                                Edit Trip
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#smartTab">
                                Smart Assign
                            </button>
                        </li>

                    </ul>

                    <div class="tab-content">

                        <!-- ================= CREATE TAB ================= -->
                        <div class="tab-pane fade show active" id="createTab">

                            <form action="../controllers/trips_controller.php" method="POST">

                                <input type="hidden" name="action" value="create">

                                <h6 class="mb-3">Manual Trip Creation</h6>

                                <div class="row">

                                    <!-- Warehouse -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Warehouse</label>
                                        <select name="warehouse_id" class="form-select" required>
                                            <option value="">Select Warehouse</option>
                                            <?php while ($w = $warehouses->fetch_assoc()): ?>
                                                <option value="<?= $w['warehouse_id'] ?>">
                                                    <?= $w['warehouse_name'] ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Truck -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Truck</label>
                                        <select name="truck_id" class="form-select" required>
                                            <option value="">Select Available Truck</option>
                                            <?php while ($t = $available_trucks->fetch_assoc()): ?>
                                                <option value="<?= $t['PK_FLEET'] ?>">
                                                    <?= $t['PLATE_NUM'] ?> - <?= $t['MODEL'] ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Driver</label>
                                        <select name="driver_id" class="form-select" required>
                                            <option value="">Select Driver</option>

                                            <?php
                                            mysqli_data_seek($drivers, 0);
                                            while ($d = $drivers->fetch_assoc()):
                                            ?>
                                                <option value="<?= $d['USER_ID'] ?>">
                                                    <?= $d['USER_NAME'] ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Departure Time -->
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Departure Time</label>
                                        <input type="datetime-local"
                                            name="departure_time"
                                            class="form-control">
                                    </div>

                                </div>

                                <!-- Attach Jobs -->
                                <h6 class="mt-3">Attach Jobs</h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tripJobsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Job</th>
                                                <th width="80">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- JS will populate -->
                                        </tbody>
                                    </table>
                                </div>

                                <button type="button"
                                    class="btn btn-sm btn-primary"
                                    id="addJobBtn">
                                    + Add Job
                                </button>

                                <button type="submit"
                                    class="btn btn-success w-100 mt-3">
                                    Create Trip
                                </button>

                            </form>

                        </div>

                        <!-- ================= EDIT TAB ================= -->
                        <div class="tab-pane fade" id="editTab">

                            <h6 class="mb-3">Modify Existing Trip</h6>

                            <div class="mb-3">
                                <label class="form-label">Select Trip</label>
                                <select class="form-select">
                                    <option>Select Trip</option>
                                </select>
                            </div>

                            <div class="alert alert-secondary">
                                Trip details will load here.
                            </div>

                            <button class="btn btn-primary">
                                Update Trip
                            </button>

                        </div>

                        <!-- ================= SMART ASSIGN TAB ================= -->
                        <div class="tab-pane fade" id="smartTab">

                            <h6 class="mb-3">Automated Smart Assignment</h6>

                            <div class="mb-3">
                                <label class="form-label">Warehouse</label>
                                <select class="form-select">
                                    <option>Select Warehouse</option>
                                </select>
                            </div>

                            <div class="alert alert-info">
                                This will automatically group nearby unassigned jobs
                                into optimized trips.
                            </div>

                            <button class="btn btn-dark">
                                Run Smart Assignment
                            </button>

                            <hr>

                            <div class="mt-3">
                                <strong>Last Run Summary</strong>
                                <ul class="mt-2">
                                    <li>Jobs Grouped: 5</li>
                                    <li>Trips Created: 2</li>
                                    <li>Unassigned Remaining: 1</li>
                                </ul>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>
    <script src="../scripts/trips.js"></script>

</body>

</html>