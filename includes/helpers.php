<?php
// includes/helpers.php

/**
 * Logs an action using the ActionLog model
 * Usage: log_activity('create_product', 'Created XYZ (ID 123)');
 */
function log_activity($action, $description = '', $reference_id = null) {
    global $databaseconn;

    $user_id = $_SESSION['USER_ID'] ?? null;

    $stmt = $databaseconn->prepare("
        INSERT INTO tbl_system_logs (user_id, action, description, reference_id)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("issi", $user_id, $action, $description, $reference_id);
    $stmt->execute();
}