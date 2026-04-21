<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryModel extends Model
{
    public const STATUS_SCHEDULED  = 'Scheduled';
    public const STATUS_IN_TRANSIT = 'InTransit';
    public const STATUS_DELIVERED  = 'Delivered';
    public const STATUS_CANCELLED  = 'Cancelled';

    protected $table            = 'deliveries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'order_id',
        'rider_name',
        'route_note',
        'eta_at',
        'status',
        'proof_url',
        'delivered_at',
        'notes',
    ];

    protected $validationRules = [
        'order_id' => 'required|integer|greater_than[0]',
        'status'   => 'required|in_list[Scheduled,InTransit,Delivered,Cancelled]',
    ];
}
