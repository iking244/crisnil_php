function logSystem($conn, $user_id, $action, $description, $reference_id = null) {
    $stmt = $conn->prepare("
        INSERT INTO tbl_system_logs (user_id, action, description, reference_id)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("issi", $user_id, $action, $description, $reference_id);
    $stmt->execute();
}