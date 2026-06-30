<?php

namespace App\Models;

use CodeIgniter\Model;

class BroadcastLogModel extends Model
{
    protected $table         = 'broadcast_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'title',
        'body',
        'target',
        'total_devices',
        'sent_count',
        'failed_count',
        'delivered_count',
        'created_by',
    ];

    public function getBroadcastsWithStats(int $limit = 20): array
    {
        $rows = $this->select('
                broadcast_logs.*,
                users.username AS created_by_username
            ')
            ->join('users', 'users.id = broadcast_logs.created_by', 'left')
            ->orderBy('broadcast_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();

        return $rows;
    }
}
