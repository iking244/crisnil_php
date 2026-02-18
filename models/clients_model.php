<?php
function getActiveClients($conn) {
    $sql = "SELECT * FROM tbl_clients WHERE is_active = 1";
    return $conn->query($sql);
}
?>
