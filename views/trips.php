<?php
include "../controllers/trips_controller.php";

$active_count    = $active_trips_count    ?? 0;
$available_count = $available_trucks_count ?? 0;
$pending_count   = $pending_jobs_count    ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trips Management</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="../styles/trips-management.css"> <!-- NEW: Add this line for the page-specific styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/status_helper.php'; ?>
    <br>
    <br>
    <br>
    <br>

    <!-- Main Trips Management Content -->
    <section class="trips-management">
        <div class="page-header">
            <h2>Trips Management</h2>
            <button class="view-more-btn">+ View More</button>
        </div>
        <p class="page-description">Manage trips, trucks, and job assignments</p>

        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card blue">
                <i class="fas fa-truck stat-icon"></i>
                <div class="stat-info">
                    <h3>Active Trips</h3>
                    <span class="stat-value"><?= $active_count ?></span>
                </div>
            </div>

            <div class="stat-card green">
                <i class="fas fa-truck stat-icon"></i>
                <div class="stat-info">
                    <h3>Available Trucks</h3>
                    <span class="stat-value"><?= $available_count ?></span>
                    <span class="stat-sub">Ready</span>
                </div>
            </div>

            <div class="stat-card orange">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <div class="stat-info">
                    <h3>Pending Jobs</h3>
                    <span class="stat-value"><?= $pending_count ?></span>
                </div>
            </div>
        </div>

        <!-- Main Sections -->
        <div class="main-sections">
            <!-- Active Trips -->
            <div class="section-card active-trips">
                <div class="section-header">
                    <i class="fas fa-truck section-icon"></i>
                    <h4>Active Trips</h4>
                    <button class="view-more-btn">View More</button>
                </div>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Truck</th>
                            <th>Driver</th>
                            <th>Jobs</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($all_trips && $all_trips->num_rows > 0): ?>
                            <?php while ($trip = $all_trips->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $trip['trip_id']; ?></td>
                                    <td><?= $trip['truck_plate_number'] ?? '-'; ?></td>
                                    <td><?= $trip['driver_name'] ?? '-'; ?></td>
                                    <td><?= $trip['job_count']; ?> jobs</td> <!-- Merged from collaborator -->
                                    <td><?= renderStatusBadge($trip['status']); ?></td>
                                    <td>
                                        <a href="trip_details.php?trip_id=<?= $trip['trip_id']; ?>" 
                                           class="action-btn manage">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No trips available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Available Trucks -->
            <div class="section-card available-trucks">
                <div class="section-header">
                    <i class="fas fa-truck section-icon"></i>
                    <h4>Available Trucks</h4>
                    <button class="view-more-btn green">View More</button>
                </div>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>Truck</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($available_trucks && $available_trucks->num_rows > 0): ?>
                            <?php while ($truck = $available_trucks->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $truck['MODEL']; ?> <?= $truck['PLATE_NUM']; ?></td>
                                    <td>
                                        <button class="action-btn create-trip" onclick="openTripModal(<?= $truck['PK_FLEET']; ?>)">Create Trip</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">No available trucks.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Unscheduled Jobs -->
        <div class="unscheduled-jobs">
            <div class="section-header red">
                <i class="fas fa-clipboard-list section-icon"></i>
                <h4>Unscheduled Job Orders</h4>
                <button class="view-more-btn">View More</button>
            </div>
            <table class="section-table">
                <thead>
                    <tr>
                        <th>Job #</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th> <!-- Merged from collaborator -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($unscheduled_jobs && $unscheduled_jobs->num_rows > 0): ?>
                        <?php while ($job = $unscheduled_jobs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $job['id']; ?></td>
                                <td><?= $job['origin']; ?></td>
                                <td><?= $job['destination']; ?></td>
                                <td><?= renderStatusBadge($job['status']); ?></td> <!-- Merged from collaborator -->
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No pending jobs.</td> <!-- Updated colspan to 4 -->
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script type="text/javascript" src="../scripts/dashboard.js"></script>
    <script type="text/javascript" src="../scripts/notif.js"></script>
    <script type="text/javascript" src="../scripts/sidenav.js"></script>
    <script type="text/javascript" src="../scripts/dropdown2.js"></script>

    <div id="tripModal" class="modal">
        <div class="modal-content">
            <h3>Select Warehouse</h3>

            <form method="POST" action="../controllers/trips_controller.php">
                <input type="hidden" name="truck_id" id="modal_truck_id">

                <label>Warehouse:</label>
                <select name="warehouse_id" required>
                    <?php while ($w = $warehouses->fetch_assoc()): ?>
                        <option value="<?= $w['warehouse_id']; ?>">
                            <?= $w['warehouse_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <br><br>
                <button type="submit" class="view-btn green">
                    Create Trip
                </button>
                <button type="button" onclick="closeTripModal()">
                    Cancel
                </button>
            </form>
        </div>
    </div>
    <script>
        function openTripModal(truckId) {
            const modal = document.getElementById('tripModal');
            modal.style.display = 'flex'; // show modal
            document.getElementById('modal_truck_id').value = truckId;
        }

        function closeTripModal() {
            document.getElementById('tripModal').style.display = 'none';
        }
    </script>

</body>

</html>