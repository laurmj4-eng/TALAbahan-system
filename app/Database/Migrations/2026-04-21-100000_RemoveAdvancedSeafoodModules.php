<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveAdvancedSeafoodModules extends Migration
{
    public function up()
    {
        // Drop FK/index/column related to removed categories module.
        if ($this->db->tableExists('products') && $this->db->fieldExists('category_id', 'products')) {
            $fkRows = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->getResultArray();
            foreach ($fkRows as $row) {
                $name = $row['CONSTRAINT_NAME'] ?? '';
                if ($name !== '') {
                    $this->db->query("ALTER TABLE products DROP FOREIGN KEY `{$name}`");
                }
            }

            $idxRows = $this->db->query("SHOW INDEX FROM products WHERE Key_name = 'idx_products_category_id'")->getResultArray();
            if ($idxRows !== []) {
                $this->db->query('ALTER TABLE products DROP INDEX idx_products_category_id');
            }

            $this->db->query('ALTER TABLE products DROP COLUMN category_id');
        }

        // Drop advanced module tables no longer used in simplified owner flow.
        $this->forge->dropTable('deliveries', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('inventory_movements', true);
        $this->forge->dropTable('purchase_items', true);
        $this->forge->dropTable('purchases', true);
        $this->forge->dropTable('categories', true);
    }

    public function down()
    {
        // Recreate only the dropped column needed for backward compatibility.
        if ($this->db->tableExists('products') && ! $this->db->fieldExists('category_id', 'products')) {
            $this->db->query('ALTER TABLE products ADD COLUMN category_id INT(11) UNSIGNED NULL');
        }
    }
}
