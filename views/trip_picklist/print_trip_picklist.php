<?php

include "../config/database_conn.php";

$tripId = isset($_GET['trip_id']) ? intval($_GET['trip_id']) : 0;

$query = $databaseconn->prepare("
SELECT
    p.product_name,
    tp.pallet_id,
    sb.box_id,
    sb.batch_code,
    sb.expiry_date
FROM tbl_trip_picklist tp
JOIN tbl_stock_boxes sb ON tp.box_id = sb.box_id
JOIN tbl_products p ON sb.product_id = p.product_id
WHERE tp.trip_id = ?
ORDER BY 
    sb.expiry_date IS NULL,
    sb.expiry_date ASC,
    sb.box_id ASC
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

body{
    font-family: Arial, sans-serif;
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    border:1px solid black;
    padding:6px;
    font-size:12px;
    text-align:left;
}

th{
    background:#f0f0f0;
}

h2{
    margin-bottom:20px;
}

</style>

</head>

<body>

<h2>Trip Pick List (Trip #<?= htmlspecialchars($tripId) ?>)</h2>

<table>

<tr>
<th>Pallet</th>
<th>Product</th>
<th>Box ID</th>
<th>Batch</th>
<th>Expiry</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['pallet_id']) ?></td>

<td><?= htmlspecialchars($row['product_name']) ?></td>

<td><?= htmlspecialchars($row['box_id']) ?></td>

<td><?= htmlspecialchars($row['batch_code']) ?></td>

<td><?= htmlspecialchars($row['expiry_date']) ?></td>

</tr>

<?php endwhile; ?>

</table>

<script>
window.print();
</script>

</body>

</html>
