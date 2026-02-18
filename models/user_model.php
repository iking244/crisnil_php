<?php

/**
 * Get user statistics by role
 * @param mysqli $conn Database connection
 * @return array Stats array
 */
function getUserStats($conn) {
    $stats = [
        'admins'  => 0,
        'staff'   => 0,
        'drivers' => 0,
    ];

    $result = $conn->query("SELECT USER_ROLE, COUNT(*) as count FROM crisnil_users GROUP BY USER_ROLE");
    while ($row = $result->fetch_assoc()) {
        if ($row['USER_ROLE'] === 'Administrator') $stats['admins'] = $row['count'];
        if ($row['USER_ROLE'] === 'Staff')         $stats['staff']  = $row['count'];
        if ($row['USER_ROLE'] === 'RIDER')         $stats['drivers'] = $row['count'];
    }

    return $stats;
}

/**
 * Get all active users
 * @param mysqli $conn
 * @return mysqli_result|bool
 */
function getAllUsers($conn) {
    $sql = "SELECT USER_ID, FIRST_NAME, LAST_NAME, EMAIL_ADDRESS, PHONE_NUM, USER_ROLE, USER_STATUS 
            FROM crisnil_users 
            ORDER BY USER_ID DESC";
    return $conn->query($sql);
}

/**
 * Check if username or email already exists
 * @return bool true if exists (cannot create/update)
 */
function isUserDuplicate($conn, $username, $email, $excludeUserId = 0) {
    $sql = "SELECT USER_ID FROM crisnil_users 
            WHERE (USER_NAME = ? OR EMAIL_ADDRESS = ?) 
            AND USER_ID != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $email, $excludeUserId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

/**
 * Create a new user
 * @param mysqli $conn
 * @param array $data Associative array of user fields
 * @return bool Success
 */
function createUser($conn, $data) {
    // Check duplicate username/email
    if (isUserDuplicate($conn, $data['username'], $data['email'])) {
        error_log("Duplicate username or email attempted: " . $data['username'] . " / " . $data['email']);
        return false;
    }

    $hashedPass = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO crisnil_users 
        (FIRST_NAME, LAST_NAME, EMAIL_ADDRESS, PHONE_NUM, USER_BIO, USER_ADDRESS, USER_NAME, USER_PASSWORD, USER_ROLE, USER_STATUS) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVATED')
    ");

    $stmt->bind_param(
        "sssssssss",
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['bio'],
        $data['address'],
        $data['username'],
        $hashedPass,
        $data['role']
    );

    $result = $stmt->execute();
    if (!$result) {
        error_log("Create user failed: " . $stmt->error);
    }
    return $result;
}

/**
 * Update an existing user
 * @param mysqli $conn
 * @param array $data Must include 'user_id'
 * @return bool Success
 */
function updateUser($conn, $data) {
    $userId = (int)($data['user_id'] ?? 0);
    if ($userId <= 0) return false;

    // Prevent changing username (or check duplicate if you allow it)
    if (!empty($data['username'])) {
        if (isUserDuplicate($conn, $data['username'], $data['email'] ?? '', $userId)) {
            error_log("Duplicate username/email on update for user #$userId");
            return false;
        }
    }

    // Build dynamic query (only update fields that are provided)
    $fields = [];
    $values = [];
    $types  = '';

    $allowed = ['first_name', 'last_name', 'email', 'phone', 'bio', 'address', 'role', 'status'];
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = strtoupper($field) . " = ?";
            $values[] = $data[$field];
            $types   .= 's';  // all strings for simplicity
        }
    }

    if (empty($fields)) return false; // nothing to update

    $values[] = $userId;
    $types   .= 'i';

    $query = "UPDATE crisnil_users SET " . implode(', ', $fields) . " WHERE USER_ID = ?";
    $stmt = $conn->prepare($query);

    $stmt->bind_param($types, ...$values);

    $result = $stmt->execute();
    if (!$result) {
        error_log("Update user #$userId failed: " . $stmt->error);
    }
    return $result;
}

/**
 * Archive a user (move to archive table + delete from active)
 * @param mysqli $conn
 * @param int $id User ID
 * @return bool Success
 */
function archiveUser($conn, $id) {
    $id = (int)$id;
    if ($id <= 0) return false;

    // Check if user exists
    $check = $conn->prepare("SELECT USER_ID FROM crisnil_users WHERE USER_ID = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        return false;
    }

    // Copy to archive
    $stmt = $conn->prepare("
        INSERT INTO tbl_archived_users 
        SELECT *, NOW() as archived_at 
        FROM crisnil_users 
        WHERE USER_ID = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        error_log("Archive copy failed for user #$id");
        return false;
    }

    // Delete from active table
    $delete = $conn->prepare("DELETE FROM crisnil_users WHERE USER_ID = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    return $delete->affected_rows > 0;
}

// Note: Run this SQL once in phpMyAdmin to create the archive table if not already done:
// CREATE TABLE tbl_archived_users LIKE crisnil_users;
// ALTER TABLE tbl_archived_users ADD archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;