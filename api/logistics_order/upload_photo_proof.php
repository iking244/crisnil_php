<?php
header("Content-Type: application/json");

include "../../config/database_conn.php";

// Validate job_id
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

if (!$job_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing job_id"
    ]);
    exit;
}

// Check if photo exists
if (!isset($_FILES['photo'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No photo uploaded"
    ]);
    exit;
}

$uploadDir = "../../uploads/job_proofs/";

// Create directory if not exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$file = $_FILES['photo'];
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

// Generate unique filename
$filename = "job_" . $job_id . "_" . time() . "." . $extension;
$targetPath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to upload file"
    ]);
    exit;
}

// Save file path in database
$stmt = $databaseconn->prepare("
    UPDATE tbl_job_orders
    SET proof_photo = ?
    WHERE id = ?
");
$stmt->bind_param("si", $filename, $job_id);
$stmt->execute();

echo json_encode([
    "status" => "success",
    "message" => "Photo uploaded successfully",
    "filename" => $filename
]);
