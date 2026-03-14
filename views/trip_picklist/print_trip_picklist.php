<?php

include "../config/database_conn.php";

$tripId = $_GET['trip_id'];

$query = $databaseconn->prepare("
SELECT
    jo.delivery_sequence,
    jo.destination,
    p.product_name,
    sb.box_id,
    sb.batch_code,
    sb.expiry_date
FROM tbl_trip_picklist tp
JOIN tbl_stock_boxes sb ON tp.box_id = sb.box_id
JOIN tbl_products p ON sb.product_id = p.product_id
JOIN tbl_job_orders jo ON jo.trip_id = tp.trip_id
WHERE tp.trip_id = ?
ORDER BY jo.delivery_sequence
");

$query->bind_param("i", $tripId);
$query->execute();

$result = $query->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Trip Pick List</title>

    <style>
        body {
            font-family: Arial;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px;
            font-size: 12px;
        }

        h2 {
            margin-bottom: 20px;
        }
    </style>

</head>

<body>

    <h2>Trip Pick List</h2>

    <table>

        <tr>
            <th>Sequence</th>
            <th>Destination</th>
            <th>Product</th>
            <th>Box ID</th>
            <th>Batch</th>
            <th>Expiry</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>

            <tr>

                <td><?= $row['delivery_sequence'] ?></td>

                <td><?= $row['destination'] ?></td>

                <td><?= $row['product_name'] ?></td>

                <td><?= $row['box_id'] ?></td>

                <td><?= $row['batch_code'] ?></td>

                <td><?= $row['expiry_date'] ?></td>

            </tr>

        <?php endwhile; ?>

    </table>

    <script>
        window.print();
    </script>

</body>

</html>