<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductPaymentConstraintModel extends Model
{
    protected $table = 'product_payment_constraints';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'product_id',
        'payment_method',
        'created_at',
    ];

    public function getAllowedByProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $rows = $this->whereIn('product_id', $productIds)->findAll();
        $map = [];
        foreach ($rows as $row) {
            $id = (int) $row['product_id'];
            $map[$id] ??= [];
            $map[$id][] = strtoupper((string) $row['payment_method']);
        }

        return $map;
    }
}
