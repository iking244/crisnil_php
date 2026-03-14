<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../../config/database_conn.php";

if (!isset($_GET['trip_id'])) {
    die("Trip ID missing.");
}

$tripId = intval($_GET['trip_id']);

$query = $databaseconn->prepare("
SELECT
    jo.delivery_sequence,
    jo.origin,
    jo.destination,
    pl.pallet_code,
    p.product_name,
    sb.box_id,
    sb.batch_code,
    sb.expiry_date
FROM tbl_trip_picklist tp
JOIN tbl_stock_boxes sb ON tp.box_id = sb.box_id
JOIN tbl_products p ON sb.product_id = p.product_id
JOIN tbl_pallets pl ON tp.pallet_id = pl.pallet_id
JOIN tbl_job_orders jo ON jo.trip_id = tp.trip_id
WHERE tp.trip_id = ?
ORDER BY jo.delivery_sequence, sb.expiry_date, sb.box_id
");

if (!$query) {
    die("SQL Error: " . $databaseconn->error);
}

$query->bind_param("i", $tripId);
$query->execute();
$result = $query->get_result();

$currentStop = null;

?>

<!DOCTYPE html>
<html>

<head>

    <title>Trip Pick List</title>

    <style>
        body {
            font-family: Arial;
            margin: 40px;
        }

        h1 {
            margin-bottom: 5px;
        }

        h3 {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 13px;
        }

        th {
            background: #f2f2f2;
        }

        .stop-box {
            margin-top: 25px;
            padding: 10px;
            border: 1px solid #999;
        }

        .signature {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .sig {
            width: 200px;
            text-align: center;
        }

        .line {
            border-top: 1px solid black;
            margin-top: 40px;
        }
    </style>

</head>

<body>

    <h1>Trip Pick List</h1>
    <b>Trip #<?= htmlspecialchars($tripId) ?></b>

    <?php while ($row = $result->fetch_assoc()): ?>

        <?php if ($currentStop !== $row['delivery_sequence']): ?>

            <?php
            if ($currentStop !== null) {
                echo "</tbody></table></div>";
            }

            $currentStop = $row['delivery_sequence'];
            ?>

            <div class="stop-box">

                <h3>Stop <?= htmlspecialchars($row['delivery_sequence']) ?></h3>

                <b>Client:</b> <?= htmlspecialchars($row['client_name']) ?><br>
                <b>Destination:</b> <?= htmlspecialchars($row['destination']) ?>

                <table>

                    <thead>
                        <tr>
                            <th>Pallet</th>
                            <th>Product</th>
                            <th>Box ID</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php endif; ?>

                    <tr>

                        <td><?= htmlspecialchars($row['pallet_code']) ?></td>

                        <td><?= htmlspecialchars($row['product_name']) ?></td>

                        <td><?= htmlspecialchars($row['box_id']) ?></td>

                        <td><?= htmlspecialchars($row['batch_code']) ?></td>

                        <td><?= htmlspecialchars($row['expiry_date']) ?></td>

                    </tr>

                <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <div class="signature">

                <div class="sig">
                    <div class="line"></div>
                    Prepared By
                </div>

                <div class="sig">
                    <div class="line"></div>
                    Checked By
                </div>

                <div class="sig">
                    <div class="line"></div>
                    Driver
                </div>

            </div>

            <script>
                window.print();
            </script>

</body>

</html>