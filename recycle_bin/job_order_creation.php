<?php
include '../controllers/job_order_controller.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Order Creation</title>
    <link rel="stylesheet" href="../styles/logistics.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <header class="topbar">
        <div class="left">
            <span class="menu-icon">&#9776;</span>
            <h1>CRISNIL TRADING CORPORATION</h1>
        </div>

        <div class="right">
            <span class="module-title">JOB ORDER CREATION</span>
            <i class="fa fa-bell"></i>
        </div>
    </header>

    <div class="main-content">
        <h2>Create Job Order</h2>

        <form action="../controllers/job_order_controller.php" method="POST">


            <label>Warehouse (Origin):</label>
            <select name="warehouse_id" required>
                <option value="">Select Warehouse</option>
                <?php while ($wh = $warehouses->fetch_assoc()): ?>
                    <option value="<?= $wh['warehouse_id']; ?>">
                        <?= $wh['warehouse_name']; ?> - <?= $wh['city']; ?>
                    </option>
                <?php endwhile; ?>
            </select>


            <label>Client (Destination):</label>
            <select name="client_id" required>
                <option value="">Select Client</option>
                <?php while ($client = $clients->fetch_assoc()): ?>
                    <option value="<?= $client['client_id']; ?>">
                        <?= $client['client_name']; ?> - <?= $client['city']; ?>
                    </option>
                <?php endwhile; ?>
            </select>


            <h3>Cargo Details</h3>

            <label>Product:</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php while ($row = $products->fetch_assoc()): ?>
                    <option value="<?= $row['product_id']; ?>">
                        <?= $row['product_name']; ?> (<?= $row['unit']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Quantity:</label>
            <input type="number" name="quantity" min="1">

            <button type="submit">Create Job Order</button>
        </form>
    </div>

</body>

</html>