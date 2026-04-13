<?php
// controllers/user_controller.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database_conn.php';
require_once '../models/user_model.php';
require_once '../includes/helpers.php';   // ← our centralized logger

$action = $_GET['action'] ?? $_POST['action'] ?? null;

// =========================
// CREATE NEW USER
// =========================
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'username'   => trim($_POST['username'] ?? ''),
        'password'   => $_POST['password'] ?? '',
        'role'       => $_POST['role'] ?? '',
        'bio'        => trim($_POST['bio'] ?? ''),
        'address'    => trim($_POST['address'] ?? ''),
    ];

    log_activity('create_user_attempt', 'Attempted to create user: ' . $data['username']);

    $success = createUser($databaseconn, $data);

    if ($success) {
        log_activity(
            'create_user',
            'Successfully created user: ' . $data['username'] . 
            ' (' . $data['first_name'] . ' ' . $data['last_name'] . ', Role: ' . $data['role'] . ')'
        );
        $_SESSION['toast'] = [
            'message' => 'User created successfully!',
            'type'    => 'success'
        ];
    } else {
        log_activity(
            'create_user_failed',
            'Failed to create user: ' . $data['username'] . ' (duplicate or database error)'
        );
        $_SESSION['toast'] = [
            'message' => 'Failed to create user. Username or email already exists.',
            'type'    => 'danger'   // or 'warning', 'info'
        ];
    }

    header("Location: ../views/user_management.php");
    exit;
}

// =========================
// UPDATE USER
// =========================
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'user_id'    => (int)($_POST['user_id'] ?? 0),
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
        'role'       => $_POST['role'] ?? '',
        'bio'        => trim($_POST['bio'] ?? ''),
        'address'    => trim($_POST['address'] ?? ''),
        'status'     => $_POST['status'] ?? 'ACTIVATED',
    ];

    log_activity('update_user_attempt', 'Attempted to update user ID ' . $data['user_id']);

    $success = updateUser($databaseconn, $data);

    if ($success) {
        log_activity(
            'update_user',
            'Updated user ID ' . $data['user_id'] . 
            ' (' . $data['first_name'] . ' ' . $data['last_name'] . ', Role: ' . $data['role'] . ', Status: ' . $data['status'] . ')'
        );
        $_SESSION['success'] = "User updated successfully!";
    } else {
        log_activity('update_user_failed', 'Failed to update user ID ' . $data['user_id']);
        $_SESSION['error'] = "Failed to update user.";
    }

    header("Location: ../views/user_management.php");
    exit;
}

// =========================
// ARCHIVE / DELETE USER
// =========================
if ($action === 'archive' && isset($_GET['id'])) {

    $user_id = (int)$_GET['id'];

    log_activity('archive_user_attempt', 'Attempted to archive user ID ' . $user_id);

    $success = archiveUser($databaseconn, $user_id);

    if ($success) {
        log_activity('archive_user', 'Archived user ID ' . $user_id);
        $_SESSION['success'] = "User archived successfully.";
    } else {
        log_activity('archive_user_failed', 'Failed to archive user ID ' . $user_id);
        $_SESSION['error'] = "Failed to archive user.";
    }

    header("Location: ../views/user_management.php");
    exit;
}

// If no valid action
header("Location: ../views/user_management.php");
exit;