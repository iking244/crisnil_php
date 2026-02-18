<?php
include "../controllers/inbound_product_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory List - Crisnil</title>
    <link rel="stylesheet" type="text/css" href="../styles/inventorylist2.css">
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
                                $notif_query = "SELECT DISTINCT notif_title, notif_desc, notif_time from tbl_notif WHERE notif_time = CURRENT_DATE"; //CURRENT DAY NOTIF.
                                $notif_execute = mysqli_query($databaseconn, $notif_query);

                                while ($notif_row = mysqli_fetch_assoc($notif_execute)) {
                                    $return_title = $notif_row['notif_title'];
                                    $return_desc = $notif_row['notif_desc'];
                                    $return_time = $notif_row['notif_time'];

                                    echo "<li><h3>$return_title</h5>$return_desc</li>";
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
            <h1>INVENTORY REPORT</h1>

            <div class="report-header">
                <label for="asOfDate">As of Date:</label>
                <input type="date" id="asOfDate">

                <button type="submit" name="search" id="searchButton">SEARCH...</button>

                <label for="totalCost">Total Cost:</label>
                <input type="text" id="totalCost" value="₱<?= $return_totalboxcost ?>" readonly>

                <label for="itemsFound">No. of Items Found:</label>
                <input type="text" id="itemsFound" value="<?= $return_availqty ?>" readonly>

            </div>

            <div class="report-table-container">
                <div style="overflow-y:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Product Description</th>
                                <th>Qty</th>
                                <th>Manufacturer Code</th>
                                <th>Manufacturer</th>
                                <th>Distributor</th>
                                <th>Status</th>
                                <th>Manufacturing Date</th>
                                <th>Expiry Date</th>
                                <th>COGS/Box</th>
                                <th>Last Updated</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['PRODUCT_CODE']) ?></td>
                                    <td><?= htmlspecialchars($item['PROD_DESCRIPTION']) ?></td>
                                    <td><?= htmlspecialchars($item['QUANTITY']) ?></td>
                                    <td><?= htmlspecialchars($item['MANUF_CODE']) ?></td>
                                    <td><?= htmlspecialchars($item['MANUFACTURER']) ?></td>
                                    <td><?= htmlspecialchars($item['DISTRIBUTOR']) ?></td>
                                    <td><?= htmlspecialchars($item['INV_STATUS']) ?></td>
                                    <td><?= htmlspecialchars($item['MANUF_DATE']) ?></td>
                                    <td><?= htmlspecialchars($item['EXPIRE_DATE']) ?></td>
                                    <td>₱<?= htmlspecialchars($item['COST_BOX']) ?></td>
                                    <td><?= htmlspecialchars($item['INV_UDATE']) ?></td>
                                    <td>
                                        <a href="inbound_product_edit.php?EditInboundID=<?= $item['INBOUNDITEM_ID'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </a> |
                                        <a href="inbound_product_delete.php?DelInboundID=<?= $item['INBOUNDITEM_ID'] ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            </div>



            <button id="exportButton">EXPORT AS...</button>
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
        <!-- <script type="text/javascript" src="scripts/inventorylist2.js"></script> -->


        <div class="user_data">
            <?php
            if (isset($_SESSION['USER_ID'])) {
                $UID = $_SESSION['USER_ID'];
                $query = "SELECT * FROM crisnil_users WHERE USER_ID = {$UID}";
                $UID_check = mysqli_query($databaseconn, $query);

                while ($row = mysqli_fetch_assoc($UID_check)) {
                    $return_uid = $row['USER_ID'];
                    //$return_name = $row['NAME'];
                    $return_username = $row['USER_NAME'];
                    $return_userpassword = $row['USER_PASSWORD'];
                    $return_userrole = $row['USER_ROLE'];

                    echo "<p><b>$return_username</b></p>";
                    echo "<p>(" . $return_userrole . ")</p>";
                }
            } else {
                header("Location index.php");
                exit();
            }
            ?>
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