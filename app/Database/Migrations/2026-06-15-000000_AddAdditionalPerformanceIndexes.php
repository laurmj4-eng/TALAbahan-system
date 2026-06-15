<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdditionalPerformanceIndexes extends Migration
{
    public function up()
    {
        $indexes = [
            'products' => [
                'idx_products_is_available' => '(`is_available`)',
                'idx_products_created_at'   => '(`created_at`)',
            ],
            'order_reviews' => [
                'idx_order_reviews_order_id' => '(`order_id`)',
            ],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($tableIndexes as $name => $cols) {
                // Check if index exists first to avoid errors (simplified)
                try {
                    $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$name}` {$cols}");
                } catch (\Exception $e) {
                    // Index might already exist
                }
            }
        }
    }

    public function down()
    {
        $drops = [
            'products'      => ['idx_products_is_available', 'idx_products_created_at'],
            'order_reviews' => ['idx_order_reviews_order_id'],
        ];

        foreach ($drops as $table => $names) {
            if (!$this->db->tableExists($table)) {
                continue;
            }
            foreach ($names as $name) {
                try {
                    $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }
        }
    }
}
