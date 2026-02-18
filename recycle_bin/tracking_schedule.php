<?php
include "../controllers/tracking_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Schedule - Crisnil</title>
    <link rel="stylesheet" type="text/css" href="../styles/trackingsched2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>

    <div class="container">
        <header>
            <div class="header1">
                <!--<h1>Dashboard</h1>-->
            </div>

            <div id="main">
                <span class="toggle-btn" onclick="toggleNav()">&#9776;</span>
            </div>

            <div class="header-content">
                <div class="header-left">
                    <img src="../imgs/imgsroles/whitelogo.png" alt="Crisnil Logo" class="logo">
                    <h1>CRISNIL TRADING CORPORATION</h1>
                </div>

                <div class="notification-container">
                    <i class="fa fa-bell notification-icon" onclick="toggleNotificationPopup()"></i>
                    <div id="notificationPopup" class="notification-popup">
                        <h1>Notifications</h1>
                        <div class="today-label">
                            <h1>Today</h1>
                        </div>
                        <div style="overflow-y:auto; height: 600px;">
                            <ul>
                                <?php
                                if ($today_notifications && mysqli_num_rows($today_notifications) > 0) {
                                    while ($notif_row = mysqli_fetch_assoc($today_notifications)) {
                                        $return_title = $notif_row['notif_title'];
                                        $return_desc = $notif_row['notif_desc'];

                                        echo "<li><h3>$return_title</h3>$return_desc</li>";
                                    }
                                } else {
                                    echo "<li>No notifications today.</li>";
                                }
                                ?>

                            </ul>
                        </div>

                        <div class="view-all-notifications">
                            <a href="admin_notification.php">View All Notifications</a>
                        </div>
                    </div>
                </div>
        </header>


        <div class="inventory-report-container">
            <h1>Tracking Schedule</h1>

            <div class="report-header">
                <form method="GET" action="tracking_schedule.php" class="search-form">
                    <label for="asOfDate">Tracking Number Search:</label>
                    <input type="text" name="search" id="asOfSched">

                    <button id="searchButton">SEARCH...</button>
                </form>
            </div>

            <div class="report-table-container">
                <div style="overflow-y:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tracking Number</th>
                                <th>Plate Number</th>
                                <th>Driver</th>
                                <th>Helper</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Status as of</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                                        <td><?= htmlspecialchars($row['plate_number']) ?></td>
                                        <td><?= htmlspecialchars($row['driver']) ?></td>
                                        <td><?= htmlspecialchars($row['helper']) ?></td>
                                        <td><?= htmlspecialchars($row['tracking_date']) ?></td>
                                        <td><?= htmlspecialchars($row['track_status']) ?></td>
                                        <td><?= htmlspecialchars($row['status_asof']) ?></td>
                                        <td>
                                            <?php
                                            $trackingNumber = $row['tracking_number'];

                                            // If cancelled or completed → history only
                                            if (
                                                $row['track_status'] === 'Cancelled Schedule' ||
                                                $row['track_status'] === 'Completed Transfer of Products'
                                            ) {
                                            ?>
                                                <a href="tracking_schedule_history.php?tracking_num_view=<?= $trackingNumber ?>">
                                                    VIEW TRACKING HISTORY
                                                </a>
                                                <?php
                                            } else {
                                                // Active schedule
                                                if ($isAdmin) {
                                                ?>
                                                    <a href="tracking_schedule_update.php?tracking_num_update=<?= $trackingNumber ?>">
                                                        <i class="fa fa-clock-o"></i> |
                                                    </a>
                                                    <a href="tracking_schedule_cancel.php?tracking_num_cancel=<?= $trackingNumber ?>">
                                                        <i class="fa fa-window-close"></i> |
                                                    </a>
                                                <?php
                                                }
                                                ?>
                                                <a href="tracking_schedule_history.php?tracking_num_view=<?= $trackingNumber ?>">
                                                    VIEW TRACKING HISTORY
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No Tracking Schedules found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>




        <div id="mySidenav" class="sidenav">
            <a href="dashboard.php">Dashboard</a>
            <a href="tracking_schedule.php">Tracking Records</a>
            <a href="inbound_product_list.php">Inventory</a>
            <a href="product_list_settings.php">Products</a>

            <div class="logout-link">
                <a href="../logout_action.php">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>




        <script type="text/javascript" src="../scripts/notif.js"></script>
        <script type="text/javascript" src="../scripts/sidenav.js"></script>
        <script type="text/javascript" src="../scripts/dropdown2.js"></script>
        <script type="text/javascript" src="../scripts/trackingsched2.js"></script>


        <div class="user_data">
            <p><b><?= htmlspecialchars($username) ?></b></p>
            <p>(<?= htmlspecialchars($userrole) ?>)</p>

            <!-- <p><b>Evalene Belino</b></p>
        <p>(Administrator)</p> -->
        </div>

        <div class="admin_dashboard_main_ui">

            <div class="admin_tracking_section">
                <!-- Code for tracking section -->
            </div>
            <div class="admin_inventory_section">
                <!-- Code for inventory section -->
            </div>
            <div class="admin_sales_section">
                <!-- Code for sales section -->
            </div>
        </div>



        <footer>
            <div class="footer1">
                <h3></h3>
            </div>
        </footer>
    </div>

</body>

</html>