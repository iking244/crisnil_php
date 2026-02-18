<?php 
    SESSION_START();
    include "../config/database_conn.php";

    if (isset($_POST['addproductlist'])){
        echo "<script type='text/javascript'>window.location.href = 'product_list_create.php';</script>";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List Settings - Crisnil</title>
    <link rel="stylesheet" type="text/css" href="../styles/inventorylist2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                <ul>
                        <li>Notification 1</li>
                        <li>Notification 2</li>
                        <li>Notification 3</li>
                    </ul>

                    <div class="view-all-notifications">
                    <a href="admin_notification.php">View All Notifications</a>
                </div>
            </div>
        </div>
    </header>


    <div class="inventory-report-container">
    <h1>Product List</h1>

    <div class="report-header">

    </div>

    <div class="report-table-container">
    <div style="overflow-y:auto;">
        <table>
            <thead>
                <tr>
                    <th>Product Code</th>
                    <th>Product Description</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <!-- Data will be dynamically inserted -->
                <tr>
                    <?php
                         $prod_listquery = "SELECT * FROM tbl_prodlist";
                         $prodlist_exec = mysqli_query($databaseconn, $prod_listquery);

                         while ($row = mysqli_fetch_assoc($prodlist_exec)){
                            $return_prodlist_pk = $row['PK_PROD_LIST'];
                            $return_prodlist_code = $row['PROD_LIST_CODE'];
                            $return_prodlist_desc = $row['PROD_LIST_DESC'];

                            echo "<tr>";
                            echo "<td>$return_prodlist_code</td>";
                            echo "<td>$return_prodlist_desc</td>";
                            echo "<td><a href='product_list_edit.php?EditProdlistID={$return_prodlist_pk}' onclick='itemedit()'>EDIT</a> | <a href='product_list_delete.php?DelProdlistID={$return_prodlist_pk}' onclick='itemdel()'>DELETE</a></td>";
                            echo "</tr>";
                         }

                    ?>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    <form action="" method="POST">
        <button id="exportButton" name="addproductlist">ADD NEW PRODUCT TO PRODUCT LIST</button>
    </form>
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
            if (isset($_SESSION['USER_ID'])){
                $UID = $_SESSION['USER_ID'];
                $query = "SELECT * FROM crisnil_users WHERE USER_ID = {$UID}";
                $UID_check = mysqli_query($databaseconn, $query);

                while ($row = mysqli_fetch_assoc($UID_check)){
                    $return_uid = $row['USER_ID'];
                    //$return_name = $row['NAME'];
                    $return_username = $row['USER_NAME'];
                    $return_userpassword = $row['USER_PASSWORD'];
                    $return_userrole = $row['USER_ROLE'];

                    echo "<p><b>$return_username</b></p>";
                    echo "<p>(". $return_userrole . ")</p>";
                }
            }

            else{
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
