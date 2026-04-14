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


    $success = createUser($databaseconn, $data);
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


  updateUser($databaseconn, $data);


    header("Location: ../views/user_management.php");
    exit;
}

// =========================
// ARCHIVE / DELETE USER
// =========================
if ($action === 'archive' && isset($_GET['id'])) {

    $user_id = (int)$_GET['id'];



    $success = archiveUser($databaseconn, $user_id);

    header("Location: ../views/user_management.php");
    exit;
}

// If no valid action
header("Location: ../views/user_management.php");
exit;