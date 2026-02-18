<?php
header("Content-Type: application/json");
require_once "db_connection.php";

if (!isset($_FILES['photo'], $_POST['tracking_id'])) {
    exit(json_encode([
        "status" => "error",
        "message" => "Missing parameters"
    ]));
}

$trackingId = $_POST['tracking_id'];
$uploadDir = "../uploads/proof/";

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

// Validate image
$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
    exit(json_encode([
        "status" => "error",
        "message" => "Invalid image type"
    ]));
}

$filename = "proof_" . $trackingId . "_" . time() . ".jpg";
$target = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
    exit(json_encode([
        "status" => "error",
        "message" => "Upload failed"
    ]));
}

$imageUrl = "https://crisnil.store/uploads/proof/delivery/" . $filename;

// Save proof
$stmt = $conn->prepare("
    UPDATE tbl_tracking
    SET 
        photo_proof_url = ?,
        photo_uploaded_at = NOW(),
        track_status = 'Completed Transfer of Products'
    WHERE tracking_number = ?
");
$stmt->bind_param("ss", $imageUrl, $trackingId);
$stmt->execute();

echo json_encode([
    "status" => "success",
    "image_url" => $imageUrl
]);
