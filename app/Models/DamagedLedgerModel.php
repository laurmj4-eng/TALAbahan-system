<?php

namespace App\Models;

use CodeIgniter\Model;

class DamagedLedgerModel extends Model
{
    protected $table            = 'damaged_ledger';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    protected $allowedFields = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_loss',
        'notes',
        'recorded_by',
    ];

    /**
     * Record a damaged in transit entry with all order items
     */
    public function recordDamagedOrder(int $orderId, array $orderItems, string $recordedBy = null): bool
    {
        $db = db_connect();
        $db->transBegin();

        try {
            foreach ($orderItems as $item) {
                $totalLoss = (float) $item['unit_price'] * (float) $item['quantity'];
                
                $this->insert([
                    'order_id'     => $orderId,
                    'product_id'   => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'] ?? 'Unknown Product',
                    'quantity'     => (float) $item['quantity'],
                    'unit_price'   => (float) $item['unit_price'],
                    'total_loss'   => $totalLoss,
                    'notes'        => 'Damaged in transit',
                    'recorded_by'  => $recordedBy,
                ]);
            }

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[DamagedLedger] Failed to record damaged order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all damaged ledger entries
     */
    public function getAllDamagedEntries(int $limit = 50, int $offset = 0): array
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get total loss for a specific date range
     */
    public function getTotalLoss(string $startDate, string $endDate): float
    {
        $result = $this->selectSum('total_loss')
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->first();

        return (float) ($result['total_loss'] ?? 0);
    }

    /**
     * Check if an order has damaged entries
     */
    public function hasDamagedEntry(int $orderId): bool
    {
        return $this->where('order_id', $orderId)->countAllResults() > 0;
    }
}
