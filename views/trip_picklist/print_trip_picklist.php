<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../config/database_conn.php";

if (!isset($_GET['trip_id'])) {
    die("Error: trip_id not provided");
}

$tripId = intval($_GET['trip_id']);

$query = $databaseconn->prepare("
SELECT
    pl.pallet_code,
    p.product_name,
    sb.box_id,
    sb.batch_code,
    sb.expiry_date
FROM tbl_trip_picklist tp
JOIN tbl_stock_boxes sb ON tp.box_id = sb.box_id
JOIN tbl_products p ON sb.product_id = p.product_id
JOIN tbl_pallets pl ON tp.pallet_id = pl.pallet_id
WHERE tp.trip_id = ?
ORDER BY 
    sb.expiry_date IS NULL,
    sb.expiry_date ASC,
    sb.box_id ASC
");

if (!$query) {
    die("Prepare failed: " . $databaseconn->error);
}

$query->bind_param("i", $tripId);

if (!$query->execute()) {
    die("Execute failed: " . $query->error);
}

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
<th>Pallet Code</th>
<th>Product</th>
<th>Box ID</th>
<th>Batch</th>
<th>Expiry</th>
</tr>

<?php if ($result->num_rows == 0): ?>

<tr>
<td colspan="5">No picklist data found.</td>
</tr>

<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($row['pallet_code']) ?></td>

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
