<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'order_id', 'product_id', 'product_name', 'unit', 'quantity', 'unit_price', 'subtotal', 'cost_price'
    ];

    protected $validationRules = [
        'order_id'      => 'required|integer|greater_than[0]',
        'product_id'    => 'permit_empty|integer|greater_than[0]',
        'product_name'  => 'required|min_length[2]|max_length[120]',
        'quantity'      => 'required|decimal|greater_than[0]',
        'unit_price'    => 'required|decimal|greater_than_equal_to[0]',
        'subtotal'      => 'required|decimal|greater_than_equal_to[0]',
    ];

    /**
     * Get all line items for a specific order.
     */
    public function getItemsByOrder($orderId)
    {
        return $this->where('order_id', $orderId)->findAll();
    }

    public function createBatchForOrder(int $orderId, array $items): bool
    {
        if ($orderId <= 0 || $items === []) {
            return false;
        }

        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                'order_id'     => $orderId,
                'product_id'   => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'unit'         => $item['unit'] ?? 'piece',
                'quantity'     => $item['quantity'],
                'unit_price'   => $item['unit_price'],
                'subtotal'     => $item['subtotal'],
            ];
        }

        return $this->insertBatch($rows) !== false;
    }

    /**
     * Get total quantity sold for a product.
     */
    public function getTotalQtySoldByProduct(int $productId): float
    {
        $result = $this->selectSum('quantity')
            ->where('product_id', $productId)
            ->first();

        return (float) ($result['quantity'] ?? 0);
    }

    /**
     * Get total revenue from a product.
     */
    public function getTotalRevenueByProduct(int $productId): float
    {
        $result = $this->selectSum('subtotal')
            ->where('product_id', $productId)
            ->first();

        return (float) ($result['subtotal'] ?? 0);
    }

    /**
     * Get complete summary for an order.
     */
    public function getOrderSummary(int $orderId): array
    {
        return $this->db->table('order_items')
            ->select('SUM(quantity) as total_items, SUM(subtotal) as total_value, COUNT(id) as line_count')
            ->where('order_id', $orderId)
            ->groupBy('order_id')
            ->get()
            ->getRowArray() ?? ['total_items' => 0, 'total_value' => 0, 'line_count' => 0];
    }

    /**
     * Get top ordered products.
     */
    public function getTopProducts(int $limit = 10): array
    {
        return $this->db->table('order_items')
            ->select('product_id, product_name, SUM(quantity) as times_ordered, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderBy('times_ordered', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
