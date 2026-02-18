<?php
function getActiveWarehouses($conn) {
    $sql = "SELECT * FROM tbl_warehouses WHERE is_active = 1";
    return $conn->query($sql);
}
?>
