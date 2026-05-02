<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentAttemptModel extends Model
{
    protected $table = 'payment_attempts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_id',
        'payment_method',
        'provider',
        'amount',
        'status',
        'reference',
        'message',
        'created_at',
    ];
}
