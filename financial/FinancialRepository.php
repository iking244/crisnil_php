<?php
class FinancialRepository
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getProductById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tbl_products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function insertOrderItem($data)
    {

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

    public function getItemsByOrderId($jobOrderId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tbl_job_order_items WHERE job_order_id = ?
        ");
        $stmt->bind_param("i", $jobOrderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateOrderTotals($jobOrderId, $subtotal, $grandTotal)
    {

        $stmt = $this->db->prepare("
            UPDATE tbl_job_orders
            SET subtotal = ?, grand_total = ?
            WHERE id = ?
        ");

        $stmt->bind_param("ddi", $subtotal, $grandTotal, $jobOrderId);
        $stmt->execute();
    }

    public function getSalesToday()
    {
        $stmt = $this->db->prepare("
        SELECT IFNULL(SUM(grand_total), 0) AS total
        FROM tbl_job_orders
        WHERE status = 'completed'
        AND DATE(updated_at) = CURDATE()
    ");

        if (!$stmt) {
            return 0;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (float) ($row['total'] ?? 0);
    }

    public function getOrdersToday()
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) AS total
        FROM tbl_job_orders
        WHERE status = 'completed'
        AND DATE(updated_at) = CURDATE()
    ");

        if (!$stmt) {
            return 0;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    public function getMonthlyRevenue()
    {
        $stmt = $this->db->prepare("
        SELECT IFNULL(SUM(grand_total), 0) AS total
        FROM tbl_job_orders
        WHERE status = 'completed'
        AND MONTH(updated_at) = MONTH(CURDATE())
        AND YEAR(updated_at) = YEAR(CURDATE())
    ");

        if (!$stmt) {
            return 0;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (float) ($row['total'] ?? 0);
    }

    public function getSalesLast7Days()
    {
        $stmt = $this->db->prepare("
        SELECT 
            DATE(updated_at) AS sale_date,
            IFNULL(SUM(grand_total), 0) AS total
        FROM tbl_job_orders
        WHERE status = 'completed'
        AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(updated_at)
        ORDER BY DATE(updated_at) ASC
    ");

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[$row['sale_date']] = (float)$row['total'];
        }

        $stmt->close();

        return $data;
    }

    public function getSalesYesterday()
    {
        $stmt = $this->db->prepare("
        SELECT IFNULL(SUM(grand_total), 0) AS total
        FROM tbl_job_orders
        WHERE status = 'completed'
        AND DATE(updated_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");

        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return (float)($row['total'] ?? 0);
    }
}
