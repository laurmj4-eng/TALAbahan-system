<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherRedemptionModel extends Model
{
    protected $table = 'voucher_redemptions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'voucher_id',
        'order_id',
        'customer_name',
        'discount_amount',
        'created_at',
    ];
}
