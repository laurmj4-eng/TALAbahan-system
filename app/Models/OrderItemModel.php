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
        'order_id', 'product_id', 'product_name', 'unit', 'quantity', 'unit_price', 'subtotal'
    ];

    /**
     * Get all line items for a specific order.
     */
    public function getItemsByOrder($orderId)
    {
        return $this->where('order_id', $orderId)->findAll();
    }
}
