<?php
session_start();
include "../config/database_conn.php";
include "../models/tracking_model.php";

if (!isset($_SESSION['USER_ID'])) {
    header("Location: ../index.php");
    exit();
}

$UID = (int) $_SESSION['USER_ID'];
$search = $_GET['search'] ?? '';

// user role
$user = getUserRole($databaseconn, $UID);
$isAdmin = ($user && $user['USER_ROLE'] === 'Administrator');

// tracking data
$result = getTrackingSchedules($databaseconn, $search);

// notifications
$today_notifications = getTodayNotifications($databaseconn);

// user display
$userData = getUserData($databaseconn, $UID);
$username = $userData['USER_NAME'] ?? '';
$userrole = $userData['USER_ROLE'] ?? '';
