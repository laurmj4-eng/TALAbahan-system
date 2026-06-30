<?php

namespace App\Models;

use CodeIgniter\Model;

class BroadcastReceiptModel extends Model
{
    protected $table         = 'broadcast_receipts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'broadcast_id',
        'token',
        'user_id',
        'username',
        'email',
        'device_model',
        'status',
        'fcm_error',
        'delivered_at',
    ];

    public function markDelivered(string $token, int $broadcastId): bool
    {
        $now = date('Y-m-d H:i:s');
        $updated = (bool) $this->where('token', $token)
            ->where('broadcast_id', $broadcastId)
            ->where('status', 'sent')
            ->set([
                'status'       => 'delivered',
                'delivered_at' => $now,
            ])
            ->update();

        if ($updated) {
            // Increment delivered_count on the parent broadcast
            $db = \Config\Database::connect();
            $db->query('UPDATE broadcast_logs SET delivered_count = delivered_count + 1 WHERE id = ?', [$broadcastId]);
        }

        return $updated;
    }

    public function getReceiptsByBroadcast(int $broadcastId): array
    {
        return $this->where('broadcast_id', $broadcastId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}
