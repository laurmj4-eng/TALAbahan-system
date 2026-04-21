<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true; 

    protected $allowedFields = [
        'transaction_code', 'customer_name', 'total_amount', 'status', 'notes'
    ];

    /**
     * Fetch all orders with the count of items in each order.
     * This powers the main Order Tracking table.
     */
    public function getOrdersWithItemCount()
    {
        return $this->db->table('orders o')
            ->select('o.*, COUNT(oi.id) as item_count')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->groupBy('o.id')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get a single order with all its line items.
     * Used for the detail/receipt modal.
     */
    public function getOrderWithItems($orderId)
    {
        $order = $this->find($orderId);
        if (!$order) return null;

        $order['items'] = $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->get()
            ->getResultArray();

        return $order;
    }

    /**
     * Get today's revenue (sum of completed orders).
     */
    public function getTodayRevenue()
    {
        $result = $this->selectSum('total_amount')
            ->where('status', 'Completed')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->first();
        return $result['total_amount'] ?? 0;
    }

    /**
     * Get count of orders by status.
     */
    public function getCountByStatus($status)
    {
        return $this->where('status', $status)->countAllResults();
    }
}