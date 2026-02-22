<?php
    require_once __DIR__ . '/config/database_conn.php';
    require_once __DIR__ . '/models/ActionLogModel.php';

    $actionLog = new ActionLog($databaseconn);

    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    $actionLog->create(
        $user_id,
        'logout',
        'User logged out from the system'
    );

    SESSION_START();

    SESSION_UNSET();
    SESSION_DESTROY();

    header("Location: index.php");
?>