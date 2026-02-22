<?php
class FinancialService
{

    private $repo;

    public function __construct($repo)
    {
        $this->repo = $repo;
    }

    public function  addItem($jobOrderId, $productId, $quantity)
    {

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

    public function recomputeTotals($jobOrderId)
    {

        $items = $this->repo->getItemsByOrderId($jobOrderId);

        $subtotal = array_sum(array_column($items, 'total_price'));
        $grandTotal = $subtotal;

        $this->repo->updateOrderTotals($jobOrderId, $subtotal, $grandTotal);
    }

    public function getSalesTrend()
    {
        $rawData = $this->repo->getSalesLast7Days();

        $trend = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('D', strtotime($date));
            $trend[] = $rawData[$date] ?? 0;
        }

        return [
            'labels' => $labels,
            'data'   => $trend
        ];
    }

    public function getDashboardMetrics()
    {
        return [
            'sales_today'      => $this->repo->getSalesToday(),
            'orders_today'     => $this->repo->getOrdersToday(),
            'monthly_revenue'  => $this->repo->getMonthlyRevenue(),
            'pending_orders'     => $this->repo->getPendingOrders()
        ];
    }

    public function getSalesComparison()
{
    $today = $this->repo->getSalesToday();
    $yesterday = $this->repo->getSalesYesterday();

    if ($yesterday == 0) {
        return 0;
    }

    return round((($today - $yesterday) / $yesterday) * 100, 1);
}

public function addItemWithoutRecompute($jobOrderId, $productId, $quantity)
{
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
}
}
