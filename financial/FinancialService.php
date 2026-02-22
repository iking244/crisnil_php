<?php
class FinancialService {

    private $repo;

    public function __construct($repo) {
        $this->repo = $repo;
    }

    public function addItem($jobOrderId, $productId, $quantity) {

        $product = $this->repo->getProductById($productId);

        $unitPrice = $product['unit_price'];
        $costPrice = $product['cost_price'];

        $totalPrice = $unitPrice * $quantity;
        $totalCost  = $costPrice * $quantity;
        $profit     = $totalPrice - $totalCost;

        $this->repo->insertOrderItem([
            'job_order_id' => $jobOrderId,
            'product_id'   => $productId,
            'quantity'     => $quantity,
            'unit_price'   => $unitPrice,
            'cost_price'   => $costPrice,
            'total_price'  => $totalPrice,
            'total_cost'   => $totalCost,
            'profit'       => $profit
        ]);

        $this->recomputeTotals($jobOrderId);
    }

    public function recomputeTotals($jobOrderId) {

        $items = $this->repo->getItemsByOrderId($jobOrderId);

        $subtotal = array_sum(array_column($items, 'total_price'));
        $grandTotal = $subtotal;

        $this->repo->updateOrderTotals($jobOrderId, $subtotal, $grandTotal);
    }
}