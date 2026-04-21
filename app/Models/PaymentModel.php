<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    public const STATUS_PENDING  = 'Pending';
    public const STATUS_PAID     = 'Paid';
    public const STATUS_REFUNDED = 'Refunded';

    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'order_id',
        'method',
        'status',
        'amount',
        'reference_no',
        'paid_at',
        'notes',
    ];

    protected $validationRules = [
        'order_id' => 'required|integer|greater_than[0]',
        'method'   => 'required|max_length[30]',
        'status'   => 'required|in_list[Pending,Paid,Refunded]',
        'amount'   => 'required|decimal|greater_than_equal_to[0]',
    ];

    public function recordForOrder(int $orderId, float $amount, string $method = 'Cash', string $status = self::STATUS_PAID, ?string $referenceNo = null): bool
    {
        return $this->insert([
            'order_id'      => $orderId,
            'method'        => $method,
            'status'        => $status,
            'amount'        => round($amount, 2),
            'reference_no'  => $referenceNo,
            'paid_at'       => $status === self::STATUS_PAID ? date('Y-m-d H:i:s') : null,
        ]) !== false;
    }
}
