<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderReviewModel extends Model
{
    protected $table = 'order_reviews';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'order_id',
        'customer_name',
        'rating',
        'comment',
        'media_paths',
        'created_at',
    ];

    protected $afterInsert = ['triggerProductAggregates'];
    protected $afterUpdate = ['triggerProductAggregates'];

    protected function triggerProductAggregates(array $data)
    {
        $orderModel = new \App\Models\OrderModel();
        
        if (isset($data['data']['order_id'])) {
            $orderModel->updateAggregatesForOrders([(int) $data['data']['order_id']]);
        } elseif (isset($data['id'])) {
            // If updating by ID, find the order_id
            $db = db_connect();
            $orderIds = $db->table('order_reviews')
                ->select('order_id')
                ->whereIn('id', (array) $data['id'])
                ->get()
                ->getResultArray();
            
            $ids = array_column($orderIds, 'order_id');
            if (!empty($ids)) {
                $orderModel->updateAggregatesForOrders($ids);
            }
        }
        
        return $data;
    }
}
