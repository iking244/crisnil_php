<?php
// includes/helpers.php

/**
 * Logs an action using the ActionLog model
 * Usage: log_activity('create_product', 'Created XYZ (ID 123)');
 */
function log_activity($action, $description = '', $user_id = null) {
    global $databaseconn;  // your connection variable

    if ($user_id === null && isset($_SESSION['USER_ID'])) {
        $user_id = (int)$_SESSION['USER_ID'];
    }

    require_once '../models/ActionLogModel.php';

    $logger = new ActionLog($databaseconn);

    return $logger->create($user_id, $action, $description);
}