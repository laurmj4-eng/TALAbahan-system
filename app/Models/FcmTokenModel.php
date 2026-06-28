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
        'is_trusted_admin_device',
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

    public function getActiveTokensByRole(string $role): array
    {
        return $this->select('fcm_device_tokens.*')
            ->join('users', 'users.id = fcm_device_tokens.user_id')
            ->where('users.role', $role)
            ->where('fcm_device_tokens.is_active', 1)
            ->findAll();
    }

    public function getActiveTrustedDeviceTokens(): array
    {
        return $this->where('is_trusted_admin_device', 1)
            ->where('is_active', 1)
            ->findAll();
    }

    public function setTrustedStatus(string $token, bool $trusted): bool
    {
        return (bool) $this->where('token', $token)
            ->set(['is_trusted_admin_device' => $trusted ? 1 : 0])
            ->update();
    }

    public function getTrustedStatus(string $token): ?bool
    {
        $row = $this->select('is_trusted_admin_device')
            ->where('token', $token)
            ->first();

        if ($row === null) {
            return null;
        }

        return (bool) $row['is_trusted_admin_device'];
    }
}
