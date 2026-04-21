<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table            = 'sales_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['transaction_code', 'items_summary', 'total_amount', 'created_at'];

    public function recordFromOrder(string $transactionCode, array $itemNames, float $totalAmount): bool
    {
        return $this->insert([
            'transaction_code' => $transactionCode,
            'items_summary'    => implode(', ', $itemNames),
            'total_amount'     => $totalAmount,
            'created_at'       => date('Y-m-d H:i:s'),
        ]) !== false;
    }
}