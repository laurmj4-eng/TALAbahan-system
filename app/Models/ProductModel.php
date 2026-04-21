<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'category_id', 'cost_price', 'selling_price', 
        'initial_stock', 'current_stock', 'wastage_qty', 'unit'
    ];

    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $validationRules = [
        'name'          => 'required|min_length[2]|max_length[255]',
        'cost_price'    => 'required|decimal|greater_than_equal_to[0]',
        'selling_price' => 'required|decimal|greater_than_equal_to[0]',
        'initial_stock' => 'required|decimal|greater_than_equal_to[0]',
        'current_stock' => 'required|decimal|greater_than_equal_to[0]',
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
        return $this->db->table('products p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResultArray();
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
}