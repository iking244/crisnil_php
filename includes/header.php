        <?php
        // Prevent "Undefined variable" warning on pages that don't set $today_notifications
        $today_notifications = $today_notifications ?? [];
        ?> 
    
    <header>
        <span class="toggle-btn" onclick="toggleNav()">&#9776;</span>
        <div class="header-content">
            <div class="header-left" style="margin-left: 25px;">
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

            <!-- Toast Container (top-right) -->
            <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
                <div id="toastContainer" class="toast-container"></div>
            </div>
    </header>