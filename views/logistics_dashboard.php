<?php
include "../controllers/logistics_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/base.css">
    <link rel="stylesheet" href="../styles/layout.css">
    <link rel="stylesheet" href="../styles/components.css">
    <link rel="stylesheet" href="../styles/logistics/logistics.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/status_helper.php'; ?>

    <!-- MAIN LOGISTICS LAYOUT -->
    <div class="main logistics-layout">

        <!-- LEFT: MAP -->
        <div class="logistics-map">
            <div id="map"></div>

            <!-- Floating stats -->
            <div class="map-stats">
                <h3><i class="fa fa-truck me-2"></i>Logistics Overview</h3>
                <div class="map-stat-row">
                    <span>Active Routes</span>
                    <strong><?= $stats['active_routes'] ?></strong>
                </div>
                <div class="map-stat-row">
                    <span>Orders Today</span>
                    <strong><?= $stats['orders_today'] ?></strong>
                </div>
                <div class="map-stat-row">
                    <span>Delayed</span>
                    <strong><?= $stats['delayed'] ?></strong>
                </div>
            </div>
        </div>

        <!-- RIGHT: CONTROL PANEL -->
        <div class="logistics-panel">

            <!-- Route Health -->
            <div class="panel-card health">
                <div class="panel-header">
                    <h4><i class="fa fa-heartbeat me-2"></i>Route Health</h4>
                </div>

                <ul class="health-list">
                    <li>
                        <div class="health-left">
                            <span class="dot green"></span>
                            <span>In Transit</span>
                        </div>
                        <strong>0</strong>
                    </li>

                    <li>
                        <div class="health-left">
                            <span class="dot yellow"></span>
                            <span>Arrived</span>
                        </div>
                        <strong>0</strong>
                    </li>

                    <li>
                        <div class="health-left">
                            <span class="dot gray"></span>
                            <span>Completed Today</span>
                        </div>
                        <strong>0</strong>
                    </li>

                    <li>
                        <div class="health-left">
                            <span class="dot blue"></span>
                            <span>Scheduled</span>
                        </div>
                        <strong>0</strong>
                    </li>
                </ul>

            </div>

            <!-- Active Routes -->
            <div class="panel-card routes">
                <div class="panel-header">
                    <h4><i class="fa fa-route me-2"></i>Active Routes</h4>
                </div>

                <div class="panel-body">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Trip #</th>
                                <th>Truck</th>
                                <th>Status</th>
                                <th>Departed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($active_trips && $active_trips->num_rows > 0): ?>
                                <?php while ($trip = $active_trips->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $trip['trip_id']; ?></td>
                                        <td><?= $trip['truck_plate_number']; ?></td>
                                        <td><?= renderStatusBadge($trip['status']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($trip['departure_time'])) {
                                                echo date('H:i', strtotime($trip['departure_time']));
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No active routes.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Deliveries -->
            <div class="panel-card deliveries">
                <div class="panel-header">
                    <h4><i class="fa fa-box me-2"></i>Active Deliveries</h4>
                </div>

                <div class="panel-body">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Job #</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                                <th>ETA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($job_orders && $job_orders->num_rows > 0): ?>
                                <?php while ($job = $job_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $job['id']; ?></td>
                                        <td><?= $job['origin']; ?></td>
                                        <td><?= $job['destination']; ?></td>
                                        <td><?= renderStatusBadge($job['status']); ?></td>
                                        <td>
                                            <?php
                                            if (!empty($job['eta'])) {
                                                echo date('H:i', strtotime($job['eta']));
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No active deliveries.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script src="../scripts/logistics.js"></script>
    <script
        async
        src="https://maps.googleapis.com/maps/api/js?key=<?= $GOOGLE_MAPS_API_KEY ?>&callback=initMap"
        defer>
    </script>
    <script src="../scripts/notif.js"></script>
    <script src="../scripts/sidenav.js"></script>
    <script src="../scripts/dropdown2.js"></script>

</body>

</html>