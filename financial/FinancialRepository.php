<?php
class FinancialRepository {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tbl_products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function insertOrderItem($data) {

        $stmt = $this->db->prepare("
            INSERT INTO tbl_job_order_items
            (job_order_id, product_id, quantity, unit_price, cost_price, total_price, total_cost, profit)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiiddddd",
            $data['job_order_id'],
            $data['product_id'],
            $data['quantity'],
            $data['unit_price'],
            $data['cost_price'],
            $data['total_price'],
            $data['total_cost'],
            $data['profit']
        );

        $stmt->execute();
    }

    public function getItemsByOrderId($jobOrderId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tbl_job_order_items WHERE job_order_id = ?
        ");
        $stmt->bind_param("i", $jobOrderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOrderTotals($jobOrderId, $subtotal, $grandTotal) {

        $stmt = $this->db->prepare("
            UPDATE tbl_job_orders
            SET subtotal = ?, grand_total = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ddi", $subtotal, $grandTotal, $jobOrderId);
        $stmt->execute();
    }
}