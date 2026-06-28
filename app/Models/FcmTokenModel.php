<?php

namespace App\Models;

use CodeIgniter\Model;

class FcmTokenModel extends Model
{
    protected $table         = 'fcm_device_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'token',
        'platform',
        'device_model',
        'is_active',
    ];

    public function getActiveTokensByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('is_active', 1)
            ->findAll();
    }

    public function getActiveTokensByUserId(?int $userId): array
    {
        if ($userId === null) {
            return [];
        }
        return $this->where('user_id', $userId)
            ->where('is_active', 1)
            ->findAll();
    }

    public function deactivateToken(string $token): bool
    {
        return $this->where('token', $token)->set(['is_active' => 0])->update();
    }

    public function tokenExists(string $token): bool
    {
        return $this->where('token', $token)->where('is_active', 1)->countAllResults() > 0;
    }
}
