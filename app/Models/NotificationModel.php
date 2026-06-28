<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'is_read',
        'read_at',
    ];

    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->where('id', $notificationId)
            ->where('user_id', $userId)
            ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->update();
    }

    public function getRecentNotifications(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getRecentUnread(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
