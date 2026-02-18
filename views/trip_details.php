<?php
include "../controllers/trip_details_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Details</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="../styles/dashboard2.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/status_helper.php'; ?>
    <br><br><br><br><br>
    <section class="dashboard-cards trip-grid">


        <!-- Trip Info -->
        <div class="trip-card blue">
            <h3>TRIP #<?= $trip['trip_id']; ?></h3>
            <div class="trip-card-body">

                <form method="POST" action="../controllers/trip_details_controller.php">
                    <input type="hidden" name="trip_id" value="<?= $trip['trip_id']; ?>">

                    <label>Driver</label>
                    <select name="driver_id">
                        <option value="">-- Select Driver --</option>
                        <?php while ($driver = $drivers->fetch_assoc()): ?>
                            <option value="<?= $driver['USER_ID']; ?>"
                                <?= ($driver['USER_ID'] == $trip['driver_id']) ? 'selected' : ''; ?>>
                                <?= $driver['USER_NAME']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label>Truck</label>
                    <input type="text" name="truck_plate_number"
                        value="<?= $trip['truck_plate_number']; ?>"
                        readonly>

                    <button type="submit" name="update_trip" class="primary-btn">
                        Save Trip Info
                    </button>
                </form>

            </div>
            <br>
            <div class="trip-card-body">

                <?php
                // Determine next status and button label
                if ($trip['status'] === 'loading') {
                    $next_status = 'ready_to_depart';
                    $button_text = 'Loading Complete';
                } else {
                    $next_status = 'loading';
                    $button_text = 'Start Loading';
                }
                ?>

                <form method="POST" action="../api/update_trip_status.php">
                    <input type="hidden" name="trip_id" value="<?php echo $trip['trip_id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $next_status; ?>">

                    <button type="submit" class="primary-btn">
                        <?php echo $button_text; ?>
                    </button>
                </form>

            </div>


        </div>

        <!-- Jobs in Trip -->
        <div class="trip-card red">
            <h3>JOBS IN THIS TRIP</h3>
            <div class="trip-card-body">

                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Job #</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($job = $trip_jobs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $job['id']; ?></td>
                                <td><?= $job['origin']; ?></td>
                                <td><?= $job['destination']; ?></td>
                                <td><?= renderStatusBadge($job['status']); ?></td>
                                <td>
                                    <?php if ($job['status'] !== 'in_transit'): ?>
                                        <a class="action-link"
                                            href="../controllers/trip_details_controller.php?remove_job=<?= $job['id']; ?>&trip_id=<?= $trip['trip_id']; ?>">
                                            Remove
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <!-- Add Jobs -->
        <div class="trip-card green">
            <h3>ADD UNSCHEDULED JOBS</h3>
            <div class="trip-card-body">

                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Job #</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Add</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($unscheduled_jobs && $unscheduled_jobs->num_rows > 0): ?>
                            <?php while ($job = $unscheduled_jobs->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $job['id']; ?></td>
                                    <td><?= $job['origin']; ?></td>
                                    <td><?= $job['destination']; ?></td>
                                    <td>
                                        <a class="action-link"
                                            href="../controllers/trip_details_controller.php?add_job=<?= $job['id']; ?>&trip_id=<?= $trip['trip_id']; ?>">
                                            Add
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No unscheduled jobs.</td>
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

    <?php if (isset($_GET['status'])): ?>
        <script>
            <?php if ($_GET['status'] === 'success'): ?>
                alert("Trip status updated successfully.");
            <?php elseif ($_GET['status'] === 'error'): ?>
                alert("Failed to update trip status.");
            <?php endif; ?>
        </script>
    <?php endif; ?>

</body>

</html>