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
}