<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderReviewModel extends Model
{
    protected $table = 'order_reviews';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_id',
        'customer_name',
        'rating',
        'comment',
        'media_paths',
        'created_at',
    ];
}
