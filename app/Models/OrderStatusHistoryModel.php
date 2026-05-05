<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderStatusHistoryModel extends Model
{
    protected $table            = 'order_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'order_id',
        'status_from',
        'status_to',
        'changed_by',
        'remarks'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at needed for history

    /**
     * Log a status change
     */
    public function logStatusChange($orderId, $from, $to, $changedBy, $remarks = null)
    {
        return $this->insert([
            'order_id'    => $orderId,
            'status_from' => $from,
            'status_to'   => $to,
            'changed_by'  => $changedBy,
            'remarks'     => $remarks
        ]);
    }

    /**
     * Get history for a specific order
     */
    public function getHistoryByOrder($orderId)
    {
        return $this->where('order_id', $orderId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
