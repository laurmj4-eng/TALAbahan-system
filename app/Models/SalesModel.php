<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesModel extends Model
{
    protected $table            = 'sales_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = ['transaction_code', 'items_summary', 'total_amount', 'created_at'];

    protected $validationRules = [
        'transaction_code' => 'required|min_length[5]|max_length[50]|is_unique[sales_history.transaction_code]',
        'items_summary'    => 'required|min_length[2]',
        'total_amount'     => 'required|decimal|greater_than[0]',
    ];

    public function recordFromOrder(string $transactionCode, array $itemNames, float $totalAmount): bool
    {
        if (empty($itemNames) || $totalAmount <= 0) {
            return false;
        }

        return $this->insert([
            'transaction_code' => $transactionCode,
            'items_summary'    => implode(', ', $itemNames),
            'total_amount'     => $totalAmount,
            'created_at'       => date('Y-m-d H:i:s'),
        ]) !== false;
    }

    /**
     * Get sales by date range.
     */
    public function getSalesByDateRange(string $startDate, string $endDate): array
    {
        return $this->whereBetween('DATE(created_at)', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get total sales for a date range.
     */
    public function getTotalSalesByDateRange(string $startDate, string $endDate): float
    {
        $result = $this->selectSum('total_amount')
            ->whereBetween('DATE(created_at)', [$startDate, $endDate])
            ->first();

        return (float) ($result['total_amount'] ?? 0);
    }

    /**
     * Get sales count for a date range.
     */
    public function getSalesCountByDateRange(string $startDate, string $endDate): int
    {
        return $this->whereBetween('DATE(created_at)', [$startDate, $endDate])
            ->countAllResults();
    }

    /**
     * Get average sale value.
     */
    public function getAverageSaleValue(string $startDate, string $endDate): float
    {
        $result = $this->db->table('sales_history')
            ->selectSum('total_amount', 'total_sum')
            ->selectCount('id', 'count')
            ->whereBetween('DATE(created_at)', [$startDate, $endDate])
            ->get()
            ->getRow();

        if (!$result || $result->count == 0) return 0;
        return round($result->total_sum / $result->count, 2);
    }

    /**
     * Get daily sales summary.
     */
    public function getDailySummary(string $date): array
    {
        return $this->db->table('sales_history')
            ->select('COUNT(id) as transaction_count, SUM(total_amount) as total_revenue')
            ->where('DATE(created_at)', $date)
            ->get()
            ->getRowArray();
    }

    /**
     * Get sales by transaction code.
     */
    public function getByTransactionCode(string $code): ?array
    {
        return $this->where('transaction_code', $code)->first();
    }
}