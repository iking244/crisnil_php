<?php
function getActiveDrivers($conn) {
    $sql = "SELECT USER_ID, FIRST_NAME, LAST_NAME 
            FROM crisnil_users
            WHERE USER_BIO = 'RIDER'
            ORDER BY USER_NAME ASC
            ";
    return $conn->query($sql);
}
?>
