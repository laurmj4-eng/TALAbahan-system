<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdvancedSeafoodModules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('categories', true);

        if ($this->db->fieldExists('category_id', 'products')) {
            $this->db->query('ALTER TABLE products MODIFY category_id INT(11) UNSIGNED NULL');

            $indexExists = $this->db->query("SHOW INDEX FROM products WHERE Key_name = 'idx_products_category_id'")->getResultArray();
            if ($indexExists === []) {
                $this->db->query('ALTER TABLE products ADD INDEX idx_products_category_id (category_id)');
            }

            $fkExists = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND CONSTRAINT_NAME = 'fk_products_category_id'")->getResultArray();
            if ($fkExists === []) {
                $this->db->query('ALTER TABLE products ADD CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE');
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 50],
            'supplier_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'purchase_date' => ['type' => 'DATE'],
            'total_cost' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Received'], 'default' => 'Received'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('reference_no');
        $this->forge->addKey('purchase_date');
        $this->forge->createTable('purchases', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'purchase_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'product_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'unit' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'unit_cost' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('purchase_id');
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('purchase_id', 'purchases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('purchase_items', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'movement_type' => ['type' => 'ENUM', 'constraint' => ['IN', 'OUT', 'ADJUSTMENT', 'WASTAGE']],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0.00],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'reference_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'moved_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['product_id', 'movement_type']);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_movements', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'method' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'Cash'],
            'status' => ['type' => 'ENUM', 'constraint' => ['Pending', 'Paid', 'Refunded'], 'default' => 'Pending'],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0.00],
            'reference_no' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'order_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rider_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'route_note' => ['type' => 'TEXT', 'null' => true],
            'eta_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Scheduled', 'InTransit', 'Delivered', 'Cancelled'], 'default' => 'Scheduled'],
            'proof_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'delivered_at' => ['type' => 'DATETIME', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addForeignKey('order_id', 'orders', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('deliveries', true);
    }

    public function down()
    {
        $this->forge->dropTable('deliveries', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('inventory_movements', true);
        $this->forge->dropTable('purchase_items', true);
        $this->forge->dropTable('purchases', true);
        $this->forge->dropTable('categories', true);
    }
}
