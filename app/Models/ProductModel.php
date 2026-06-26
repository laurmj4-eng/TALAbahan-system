<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'cost_price', 'selling_price', 
        'initial_stock', 'current_stock', 'wastage_qty', 'unit', 'image',
        'is_available', 'real_sold_count', 'real_rating'
    ];

    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[255]',
        'cost_price'    => 'required|numeric|greater_than_equal_to[0]',
        'selling_price' => 'required|numeric|greater_than[0]|validate_price_gt_cost',
    ];

    protected $validationMessages = [
        'selling_price' => [
            'validate_price_gt_cost' => 'Selling price must be greater than cost price.',
        ],
    ];

    // Custom method to get items with profit calculation
    public function getDailyInventory()
    {
        $products = $this->orderBy('name', 'ASC')->findAll(1000);
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

        if ((int) ($product['is_available'] ?? 0) !== 1) {
            return null;
        }

        return $product;
    }

    public function reduceStock(int $productId, float $qty): bool
    {
        // Stock tracking disabled. Always return true.
        return true;
    }

    /**
     * Increase stock (for refunds or corrections).
     */
    public function increaseStock(int $productId, float $qty): bool
    {
        // Stock tracking disabled. Always return true.
        return true;
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

    /**
     * Recalculate and update the real_sold_count and real_rating for a product.
     * This acts as our background aggregation mechanism to avoid N+1 queries.
     */
    public function updateAggregates(int $productId): void
    {
        $db = db_connect();

        // 1. Calculate real_sold_count from order_items joined with completed orders
        $soldResult = $db->table('order_items oi')
            ->selectSum('oi.quantity', 'total_sold')
            ->join('orders o', 'o.id = oi.order_id')
            ->where('oi.product_id', $productId)
            ->whereIn('o.status', [OrderModel::STATUS_COMPLETED, OrderModel::STATUS_SHIPPED])
            ->get()
            ->getRowArray();
        
        $totalSold = (int) ($soldResult['total_sold'] ?? 0);

        // 2. Calculate real_rating from order_reviews
        $ratingResult = $db->table('order_reviews r')
            ->selectAvg('r.rating', 'avg_rating')
            ->join('order_items oi', 'oi.order_id = r.order_id')
            ->where('oi.product_id', $productId)
            ->get()
            ->getRowArray();
        
        $avgRating = $ratingResult['avg_rating'] !== null ? round((float) $ratingResult['avg_rating'], 1) : null;

        // 3. Update the product
        $this->update($productId, [
            'real_sold_count' => $totalSold,
            'real_rating'     => $avgRating
        ]);

        // 4. Invalidate Dashboard Cache to immediately reflect changes
        $cache = \Config\Services::cache();
        $cache->delete('customer_dashboard_products');
    }
}