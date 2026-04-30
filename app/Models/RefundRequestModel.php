<?php

namespace App\Models;

use CodeIgniter\Model;

class RefundRequestModel extends Model
{
    protected $table = 'refund_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'order_id',
        'customer_name',
        'reason',
        'status',
        'evidence_paths',
    ];
}
