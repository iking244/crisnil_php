<?php

function getUserRole($conn, $userId) {
    $stmt = $conn->prepare(
        "SELECT USER_ROLE FROM crisnil_users WHERE USER_ID = ? LIMIT 1"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getTrackingSchedules($conn, $search = '') {
    if ($search) {
        $sql = "SELECT PK_TRKNUM, tracking_number, plate_number, driver, helper,
                       tracking_date, track_status,
                       DATE_FORMAT(status_asof, '%Y-%m-%d %h:%i %p') AS status_asof
                FROM tbl_tracking
                WHERE tracking_number LIKE ?";
        $stmt = $conn->prepare($sql);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param('s', $searchTerm);
    } else {
        $sql = "SELECT PK_TRKNUM, tracking_number, plate_number, driver, helper,
                       tracking_date, track_status,
                       DATE_FORMAT(status_asof, '%Y-%m-%d %h:%i %p') AS status_asof
                FROM tbl_tracking";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    return $stmt->get_result();
}

function getTodayNotifications($conn) {
    return mysqli_query($conn, "
        SELECT DISTINCT notif_title, notif_desc, notif_time
        FROM tbl_notif
        WHERE notif_time = CURRENT_DATE
    ");
}

function getUserData($conn, $userId) {
    $stmt = $conn->prepare(
        "SELECT USER_NAME, USER_ROLE FROM crisnil_users WHERE USER_ID = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
