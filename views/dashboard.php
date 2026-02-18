<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../controllers/dashboard_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Crisnil</title>
    <link rel="stylesheet" href="../styles/dashboard2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../imgs/imgsroles/logocrisnil.png" type="image/x-icon">
</head>

<body>
    <?php include '../includes/sidenav.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div class="app-wrapper">
        <div class=" main">



            <div class="dashboard-hero">
                <div class="hero-overlay">
                    <div class="hero-left">
                        <h1 id="greeting">Welcome, Admin</h1>
                        <p id="currentDate">Monday, Feb 10, 2026</p>
                        <p id="currentTime">10:42 AM</p>
                    </div>

                    <div class="hero-right">
                        <h3>Recent Activity</h3>
                        <ul class="hero-activity">
                            <?php
                            if ($activity_result && mysqli_num_rows($activity_result) > 0) {
                                while ($activity = mysqli_fetch_assoc($activity_result)) {
                                    echo "<li><strong>" . htmlspecialchars($activity['notif_title']) . ":</strong> "
                                        . htmlspecialchars($activity['notif_desc']) . "</li>";
                                }
                            } else {
                                echo "<li>No recent activity.</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="admin_dashboard_main_ui">
                <!-- Dashboard Title -->
                <!-- DASHBOARD STAT CARDS -->
                <div class="dashboard-cards">

                    <!-- Blue - Total Products -->
                    <div class="card blue total-products-card">
                        <div class="total-header">
                            <h3>TOTAL PRODUCTS</h3>
                        </div>
                        <div class="total-body">
                            <div class="donut-wrapper">
                                <div class="donut donut-animated" style="--donut-percent: 100;">
                                    <div class="donut-value"><?= $total_products; ?></div>
                                </div>
                            </div>
                            <div class="product-bars">
                                <?php foreach ($product_distribution as $prod): ?>
                                    <div class="bar-row">
                                        <span><?= htmlspecialchars($prod['product']); ?></span>
                                        <div class="bar">
                                            <div class="fill" style="width: <?= $prod['percent']; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="total-footer">
                            <a href="product_management.php" class="see-products-btn">See Products</a>
                        </div>
                    </div>

                    <!-- Red - Low Stock -->
                    <div class="card red low-stock-card">
                        <div class="total-header">
                            <h3>LOW STOCK ITEMS</h3>
                        </div>
                        <div class="low-stock-body">
                            <?php foreach ($product_distribution as $prod): ?>
                                <div class="stock-meter">
                                    <div class="meter"
                                        style="--color: <?= $prod['color']; ?>; --percent: <?= $prod['percent']; ?>%;">
                                        <span class="meter-value"><?= round($prod['percent']); ?>%</span>
                                    </div>
                                    <div class="meter-label"><?= htmlspecialchars($prod['product']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="low-stock-footer">
                            <p class="reminder">Please restock items below safe levels.</p>
                            <button class="card-button" 
									type="button"
									onclick="window.location.href='product_management.php'">
								View Items
							</button>
                        </div>
                    </div>

                    <!-- Green - Active Deliveries -->
                    <div class="card green active-deliveries-card">
                        <div class="total-header">
                            <h3>ACTIVE DELIVERIES</h3>
                        </div>
                        <div class="active-deliveries-body">
                            <?php
                            if (empty($delivery_distribution)) {
                                echo '<p style="color:white; text-align:center; opacity:0.8; margin: 40px 0;">No active deliveries right now.</p>';
                            } else {
                                foreach ($delivery_distribution as $del):
                                    $percent = round($del['percent']);
                            ?>
                                    <div class="status-bar-row">
                                        <div class="status-label">
                                            <?= htmlspecialchars($del['status']) ?>
                                            <span class="status-count">(<?= $del['count'] ?>)</span>
                                        </div>
                                        <div class="status-bar"> <!-- Changed from "bar" -->
                                            <div class="status-fill"
                                                style="width: <?= $percent ?>%; background: <?= htmlspecialchars($del['color'] ?? '#43a047') ?> !important;">
                                            </div>
                                        </div>
                                        <div class="percent-label"><?= $percent ?>%</div>
                                    </div>
                            <?php
                                endforeach;
                            } ?>
                        </div>
                        <div class="active-deliveries-footer">
                            <p class="reminder">Monitor ongoing deliveries.</p>
                            <a href="logistics_dashboard.php" class="card-button">View Deliveries</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script type="text/javascript" src="../scripts/dashboard.js"></script>
        <script type="text/javascript" src="../scripts/notif.js"></script>
        <script type="text/javascript" src="../scripts/sidenav.js"></script>
        <script type="text/javascript" src="../scripts/dropdown2.js"></script>




        <footer>
            <div class="footer1">
                <h3></h3>
            </div>
        </footer>
    </div>

</body>

</html>