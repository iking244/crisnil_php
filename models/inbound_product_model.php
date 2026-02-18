<?php
function getInventorySummary($conn) {
    $data = [
        'total_qty' => 0,
        'total_cost' => 0
    ];

    $query = "SELECT 
                SUM(QUANTITY) AS NUM_QTY, 
                SUM(COST_BOX) AS TOTAL_COST 
              FROM inbounditems_table";

    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $data['total_qty'] = $row['NUM_QTY'] ?? 0;
        $data['total_cost'] = $row['TOTAL_COST'] ?? 0;
    }

    return $data;
}

function getInventoryList($conn) {
    $items = [];

    $query = "SELECT 
                INBOUNDITEM_ID,
                PRODUCT_CODE,
                QUANTITY,
                MANUF_CODE,
                PROD_DESCRIPTION,
                MANUFACTURER,
                DISTRIBUTOR,
                INV_STATUS,
                MANUF_DATE,
                EXPIRE_DATE,
                COST_BOX,
                INV_UDATE
              FROM inbounditems_table";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    return $items;
}

function getTodayNotifications($conn) {
    $notifications = [];

    $query = "SELECT notif_title, notif_desc 
              FROM tbl_notif 
              WHERE notif_time = CURRENT_DATE";

    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    return $notifications;
}

function getUserData($conn, $userId) {
    $stmt = $conn->prepare(
        "SELECT USER_NAME, USER_ROLE 
         FROM crisnil_users 
         WHERE USER_ID = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
