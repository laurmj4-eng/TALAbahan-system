<?php

namespace App\Models;

use CodeIgniter\Model;

class LossesModel extends Model
{
    protected $table            = 'losses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'order_id',
        'loss_type',
        'description',
        'amount',
        'recorded_by',
    ];

    /**
     * Record a food waste loss from a cancelled order
     */
    public function recordFoodWasteLoss(int $orderId, float $amount, string $recordedBy = null, string $description = ''): bool
    {
        return $this->insert([
            'order_id'     => $orderId,
            'loss_type'    => 'Food Waste Loss',
            'description'  => $description ?: 'Order cancelled after cooking started',
            'amount'       => $amount,
            'recorded_by'  => $recordedBy,
        ]) !== false;
    }

    /**
     * Get total losses for a specific date range
     */
    public function getTotalLosses(string $startDate, string $endDate): float
    {
        $result = $this->selectSum('amount')
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->first();

        return (float) ($result['amount'] ?? 0);
    }

    /**
     * Get losses by type
     */
    public function getLossesByType(string $lossType, int $limit = 50): array
    {
        return $this->where('loss_type', $lossType)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get all losses with optional pagination
     */
    public function getAllLosses(int $limit = 50, int $offset = 0): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }
}
