<?php
include "../controllers/trips_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trips Management</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
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

    <!-- Trip Control Panel -->
    <section class="dashboard-cards" style="grid-template-columns: 1fr;">

        <div class="card blue">
            <h3>TRIP CONTROL PANEL</h3>
            <div class="card-body">

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Trip #</th>
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
                                    <td class="order-id">#<?= $trip['trip_id']; ?></td>
                                    <td><?= $trip['truck_plate_number'] ?? '-'; ?></td>
                                    <td><?= $trip['driver_name'] ?? '-'; ?></td>
                                    <td><?= $trip['job_count']; ?> jobs</td>
                                    <td>
                                        <?= renderStatusBadge($trip['status']); ?>
                                    </td>
                                    <td>
                                        <a href="trip_details.php?trip_id=<?= $trip['trip_id']; ?>" class="view-btn">
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

                <div class="card-footer">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>"
                            class="view-btn"
                            style="<?= ($i == $page) ? 'background:#1e3fa3;' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>

            </div>
        </div>

    </section>

    <section class="dashboard-cards two-column">

        <!-- Available Trucks -->
        <div class="card green">
            <h3>AVAILABLE TRUCKS</h3>
            <div class="card-body">

                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Truck</th>
                            <th>Plate Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($available_trucks && $available_trucks->num_rows > 0): ?>
                            <?php while ($truck = $available_trucks->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $truck['MODEL']; ?></td>
                                    <td><?= $truck['PLATE_NUM']; ?></td>
                                    <td>
                                        <button
                                            class="view-btn green"
                                            onclick="openTripModal(<?= $truck['PK_FLEET']; ?>)">
                                            Create Trip
                                        </button>
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
        <div class="card red">
            <h3>UNSCHEDULED JOB ORDERS</h3>
            <div class="card-body">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Job #</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($unscheduled_jobs && $unscheduled_jobs->num_rows > 0): ?>
                            <?php while ($job = $unscheduled_jobs->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $job['id']; ?></td>
                                    <td><?= $job['origin']; ?></td>
                                    <td><?= $job['destination']; ?></td>
                                    <td><?= renderStatusBadge($job['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No pending jobs.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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