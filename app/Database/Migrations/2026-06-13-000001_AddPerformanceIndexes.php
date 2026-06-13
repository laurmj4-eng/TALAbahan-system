<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        $indexes = [
            'orders' => [
                'idx_orders_status_created' => '(`status`, `created_at`)',
                'idx_orders_customer_name'  => '(`customer_name`(50))',
                'idx_orders_payment_status' => '(`payment_method`, `payment_status`)',
            ],
            'order_items' => [
                'idx_order_items_order_id'    => '(`order_id`)',
                'idx_order_items_product_id'  => '(`product_id`)',
            ],
            'users' => [
                'idx_users_email' => '(`email`(100))',
            ],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($tableIndexes as $name => $cols) {
                $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$cols}");
            }
        }
    }

    public function down()
    {
        $drops = [
            'orders'     => ['idx_orders_status_created', 'idx_orders_customer_name', 'idx_orders_payment_status'],
            'order_items'=> ['idx_order_items_order_id', 'idx_order_items_product_id'],
            'users'      => ['idx_users_email'],
        ];

        foreach ($drops as $table => $names) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($names as $name) {
                $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            }
        }
    }
}
