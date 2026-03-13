<?php
include "../controllers/warehouse_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Warehouse Receiving - CRISNIL</title>

<link rel="stylesheet" href="../styles/base.css">
<link rel="stylesheet" href="../styles/layout.css">
<link rel="stylesheet" href="../styles/components.css">
<link rel="stylesheet" href="../styles/products/products.css">
<link rel="stylesheet" href="../styles/modals.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


<body>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidenav.php'; ?>

<div class="main">

<div class="container-fluid pt-3">

<!-- HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">

<h1 class="page-title">Warehouse Receiving</h1>

<a href="inventory_overview.php" class="btn btn-outline-dark">
<i class="fa fa-arrow-left me-1"></i> Back to Inventory
</a>

</div>


<?php

$drGroups = [];

while($row = mysqli_fetch_assoc($deliveryItems)){

$dr = $row['dr_number'];

if(!isset($drGroups[$dr])){

$drGroups[$dr] = [
"items"=>[],
"total_boxes"=>0,
"assigned_boxes"=>0
];

}

$drGroups[$dr]["items"][] = $row;
$drGroups[$dr]["total_boxes"] += $row['qty'];
$drGroups[$dr]["assigned_boxes"] += $row['assigned_boxes'];

}

?>


<?php foreach($drGroups as $dr => $group):

$progress = ($group["assigned_boxes"] / $group["total_boxes"]) * 100;
$isComplete = $group["assigned_boxes"] == $group["total_boxes"];

?>

<div class="card dr-card">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-2">

<h5 class="mb-0">

DR <?= $dr ?>

<?php if($isComplete): ?>

<span class="badge bg-success ms-2">Completed</span>

<?php endif; ?>

</h5>

<small class="text-muted">

<?= $group["assigned_boxes"] ?> / <?= $group["total_boxes"] ?> boxes

</small>

</div>

<div class="progress mb-3">

<div class="progress-bar bg-success"
style="width: <?= $progress ?>%">
</div>

</div>


<?php foreach($group["items"] as $item):

$remaining = $item["qty"] - $item["assigned_boxes"];

?>

<div class="item-row">

<div class="d-flex justify-content-between">

<div>

<div class="fw-semibold">

<?= htmlspecialchars($item["product_name"]) ?>

</div>

<small class="text-muted">

<?= $item["total_weight"] ?> kg

</small>

</div>


<div>

<button class="btn btn-primary btn-sm assignBtn"

data-id="<?= $item['delivery_item_id'] ?>"
data-product="<?= htmlspecialchars($item['product_name']) ?>"
data-qty="<?= $item['qty'] ?>"

<?= $remaining == 0 ? "disabled" : "" ?>

>

<i class="fa fa-box"></i>

<?= $remaining == 0 ? "Completed" : "Assign" ?>

</button>

</div>

</div>


<div class="small mt-2">

<span class="me-3">
Boxes: <strong><?= $item["qty"] ?></strong>
</span>

<span class="me-3">
Assigned: <strong><?= $item["assigned_boxes"] ?></strong>
</span>

<span>
Remaining: <strong><?= $remaining ?></strong>
</span>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

<?php endforeach; ?>


</div>
</div>


<?php include 'modals/assign_boxes_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../scripts/utils.js"></script>
<script src="../scripts/assign_boxes.js"></script>

</body>
</html>