<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'cost_price', 'selling_price', 
        'initial_stock', 'current_stock', 'wastage_qty', 'unit', 'image'
    ];

    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[255]',
        'cost_price'    => 'required|numeric|greater_than_equal_to[0]',
        'selling_price' => 'required|numeric|greater_than[0]|validate_price_gt_cost',
        'initial_stock' => 'required|numeric|greater_than_equal_to[0]',
        'current_stock' => 'required|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'selling_price' => [
            'validate_price_gt_cost' => 'Selling price must be greater than cost price.',
        ],
    ];

    // Custom method to get items with profit calculation
    public function getDailyInventory()
    {
        $products = $this->findAll();
        foreach ($products as &$p) {
            $p['potential_profit'] = ($p['selling_price'] - $p['cost_price']) * $p['current_stock'];
            $p['sold_qty'] = $p['initial_stock'] - $p['current_stock'] - $p['wastage_qty'];
        }
        return $products;
    }

    public function getWithCategory(): array
    {
        return $this->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getSellableById(int $productId): ?array
    {
        $product = $this->find($productId);
        if (! $product) {
            return null;
        }

        if ((float) ($product['current_stock'] ?? 0) <= 0) {
            return null;
        }

        return $product;
    }

    public function reduceStock(int $productId, float $qty): bool
    {
        $product = $this->find($productId);
        if (! $product) {
            return false;
        }

        $current = (float) ($product['current_stock'] ?? 0);
        if ($qty <= 0 || $current < $qty) {
            return false;
        }

        $newStock = round($current - $qty, 2);
        return $this->update($productId, ['current_stock' => $newStock]);
    }

    /**
     * Increase stock (for refunds or corrections).
     */
    public function increaseStock(int $productId, float $qty): bool
    {
        $product = $this->find($productId);
        if (!$product || $qty <= 0) {
            return false;
        }

        $current = (float) ($product['current_stock'] ?? 0);
        $newStock = round($current + $qty, 2);
        return $this->update($productId, ['current_stock' => $newStock]);
    }

    /**
     * Get low stock products (below 10 units).
     */
    public function getLowStockProducts(float $threshold = 10): array
    {
        return $this->where('current_stock <=', $threshold)
            ->orderBy('current_stock', 'ASC')
            ->findAll();
    }

    /**
     * Get profit margin for a product.
     */
    public function getProfitMargin(int $productId): ?float
    {
        $product = $this->find($productId);
        if (!$product) return null;

        $cost = (float) ($product['cost_price'] ?? 0);
        $selling = (float) ($product['selling_price'] ?? 0);

        if ($cost == 0) return 0;
        return round((($selling - $cost) / $cost) * 100, 2);
    }

    /**
     * Get products sorted by profit margin (descending).
     */
    public function getByProfitMargin(): array
    {
        $products = $this->findAll();
        usort($products, function($a, $b) {
            $marginA = $this->getProfitMargin((int) $a['id']) ?? 0;
            $marginB = $this->getProfitMargin((int) $b['id']) ?? 0;
            return $marginB <=> $marginA;
        });
        return $products;
    }

    /**
     * Get best-selling products by quantity sold.
     */
    public function getBestSellers(int $limit = 10): array
    {
        return $this->db->table('order_items oi')
            ->select('oi.product_id, oi.product_name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue')
            ->groupBy('oi.product_id')
            ->orderBy('total_sold', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get out of stock products.
     */
    public function getOutOfStock(): array
    {
        return $this->where('current_stock <=', 0)
            ->findAll();
    }

    /**
     * Calculate inventory value.
     */
    public function getTotalInventoryValue(): float
    {
        $result = $this->db->table('products')
            ->selectSum('(cost_price * current_stock)', 'total_value')
            ->get()
            ->getRow();

        return (float) ($result->total_value ?? 0);
    }
}