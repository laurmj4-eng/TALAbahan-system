<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        $tables = [
            'orders' => [
                'idx_orders_status_created' => ['status', 'created_at'],
                'idx_orders_customer_name'  => ['customer_name'],
                'idx_orders_payment_status' => ['payment_status'],
            ],
            'order_items' => [
                'idx_order_items_order_id'    => ['order_id'],
                'idx_order_items_product_id'  => ['product_id'],
            ],
            'users' => [
                'idx_users_email' => ['email'],
            ],
        ];

        foreach ($tables as $table => $indexes) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($indexes as $name => $columns) {
                $this->forge->addKey($table, $columns, false, $name);
            }
        }
        $this->forge->createIndex();
    }

    public function down()
    {
        $indexes = [
            'orders' => ['idx_orders_status_created', 'idx_orders_customer_name', 'idx_orders_payment_status'],
            'order_items' => ['idx_order_items_order_id', 'idx_order_items_product_id'],
            'users' => ['idx_users_email'],
        ];

        foreach ($indexes as $table => $names) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($names as $name) {
                $this->forge->dropKey($table, $name);
            }
        }
    }
}
