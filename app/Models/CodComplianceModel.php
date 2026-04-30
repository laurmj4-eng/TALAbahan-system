<?php

namespace App\Models;

use CodeIgniter\Model;

class CodComplianceModel extends Model
{
    protected $table = 'cod_compliance';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'customer_name',
        'failed_cod_count',
        'window_start_at',
        'cod_disabled_until',
        'last_failed_at',
    ];

    public function getState(string $customerName): array
    {
        $row = $this->where('customer_name', $customerName)->first();
        if (! $row) {
            return [
                'failed_cod_count' => 0,
                'window_start_at' => null,
                'cod_disabled_until' => null,
            ];
        }

        return $row;
    }

    public function isCodAllowed(string $customerName): bool
    {
        $state = $this->getState($customerName);
        $disabledUntil = $state['cod_disabled_until'] ?? null;
        if (! $disabledUntil) {
            return true;
        }

        return strtotime((string) $disabledUntil) <= time();
    }

    public function markFailedCod(string $customerName, int $threshold = 3, int $windowDays = 60, int $disableDays = 14): void
    {
        $now = time();
        $state = $this->where('customer_name', $customerName)->first();
        $windowStart = $state['window_start_at'] ?? null;
        $windowExpired = true;
        if ($windowStart) {
            $windowExpired = strtotime((string) $windowStart) < strtotime("-{$windowDays} days", $now);
        }

        $nextCount = $windowExpired ? 1 : ((int) ($state['failed_cod_count'] ?? 0) + 1);
        $nextWindowStart = $windowExpired ? date('Y-m-d H:i:s', $now) : $windowStart;
        $disabledUntil = null;
        if ($nextCount >= $threshold) {
            $disabledUntil = date('Y-m-d H:i:s', strtotime("+{$disableDays} days", $now));
        }

        $payload = [
            'customer_name' => $customerName,
            'failed_cod_count' => $nextCount,
            'window_start_at' => $nextWindowStart,
            'last_failed_at' => date('Y-m-d H:i:s', $now),
            'cod_disabled_until' => $disabledUntil,
        ];

        if ($state) {
            $this->update((int) $state['id'], $payload);
            return;
        }

        $this->insert($payload);
    }
}
