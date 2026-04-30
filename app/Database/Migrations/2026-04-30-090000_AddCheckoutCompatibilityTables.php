<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCheckoutCompatibilityTables extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldNames('orders');

        if (! in_array('subtotal_amount', $fields, true)) {
            $this->forge->addColumn('orders', [
                'subtotal_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                    'after'      => 'total_amount',
                ],
            ]);
        }

        if (! in_array('shipping_fee', $fields, true)) {
            $this->forge->addColumn('orders', [
                'shipping_fee' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                    'after'      => 'subtotal_amount',
                ],
            ]);
        }

        if (! in_array('voucher_discount', $fields, true)) {
            $this->forge->addColumn('orders', [
                'voucher_discount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                    'after'      => 'shipping_fee',
                ],
            ]);
        }

        if (! in_array('final_amount', $fields, true)) {
            $this->forge->addColumn('orders', [
                'final_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                    'after'      => 'voucher_discount',
                ],
            ]);
        }

        if (! in_array('payment_status', $fields, true)) {
            $this->forge->addColumn('orders', [
                'payment_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'unpaid',
                    'after'      => 'payment_method',
                ],
            ]);
        }

        if (! in_array('payment_ref', $fields, true)) {
            $this->forge->addColumn('orders', [
                'payment_ref' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                    'after'      => 'payment_status',
                ],
            ]);
        }

        if (! in_array('payment_provider', $fields, true)) {
            $this->forge->addColumn('orders', [
                'payment_provider' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'null'       => true,
                    'after'      => 'payment_ref',
                ],
            ]);
        }

        if (! in_array('applied_vouchers', $fields, true)) {
            $this->forge->addColumn('orders', [
                'applied_vouchers' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'payment_provider',
                ],
            ]);
        }

        $shippingFields = $this->db->getFieldNames('shipping_locations');
        if (! in_array('shipping_fee', $shippingFields, true)) {
            $this->forge->addColumn('shipping_locations', [
                'shipping_fee' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 49.00,
                    'after'      => 'city_municipality',
                ],
            ]);
        }

        if (! $this->db->tableExists('vouchers')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'scope' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'platform',
                ],
                'discount_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'fixed',
                ],
                'discount_value' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                ],
                'max_discount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'min_order_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                ],
                'payment_method_limit' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'starts_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'ends_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('code');
            $this->forge->createTable('vouchers', true);
        }
        $voucherCount = $this->db->table('vouchers')->countAllResults();
        if ($voucherCount === 0) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('vouchers')->insertBatch([
                [
                    'code' => 'PLAT40',
                    'name' => 'Platform 40 Off',
                    'scope' => 'platform',
                    'discount_type' => 'fixed',
                    'discount_value' => 40,
                    'max_discount' => 40,
                    'min_order_amount' => 500,
                    'payment_method_limit' => null,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'SHOP8',
                    'name' => 'Shop 8 Percent',
                    'scope' => 'shop',
                    'discount_type' => 'percent',
                    'discount_value' => 8,
                    'max_discount' => 120,
                    'min_order_amount' => 1000,
                    'payment_method_limit' => null,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (! $this->db->tableExists('voucher_redemptions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'voucher_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'order_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'customer_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                    'null'       => true,
                ],
                'discount_amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('voucher_id');
            $this->forge->addKey('order_id');
            $this->forge->createTable('voucher_redemptions', true);
        }

        if (! $this->db->tableExists('payment_attempts')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'order_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'payment_method' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'provider' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0.00,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'pending',
                ],
                'reference' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                ],
                'message' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('order_id');
            $this->forge->createTable('payment_attempts', true);
        }

        if (! $this->db->tableExists('cod_compliance')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'customer_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 120,
                ],
                'failed_cod_count' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'window_start_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'cod_disabled_until' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'last_failed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('customer_name');
            $this->forge->createTable('cod_compliance', true);
        }

        if (! $this->db->tableExists('product_payment_constraints')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'product_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'payment_method' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('product_id');
            $this->forge->createTable('product_payment_constraints', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('product_payment_constraints')) {
            $this->forge->dropTable('product_payment_constraints', true);
        }
        if ($this->db->tableExists('cod_compliance')) {
            $this->forge->dropTable('cod_compliance', true);
        }
        if ($this->db->tableExists('payment_attempts')) {
            $this->forge->dropTable('payment_attempts', true);
        }
        if ($this->db->tableExists('voucher_redemptions')) {
            $this->forge->dropTable('voucher_redemptions', true);
        }
        if ($this->db->tableExists('vouchers')) {
            $this->forge->dropTable('vouchers', true);
        }

        $shippingFields = $this->db->getFieldNames('shipping_locations');
        if (in_array('shipping_fee', $shippingFields, true)) {
            $this->forge->dropColumn('shipping_locations', 'shipping_fee');
        }

        $orderFields = $this->db->getFieldNames('orders');
        foreach ([
            'subtotal_amount',
            'shipping_fee',
            'voucher_discount',
            'final_amount',
            'payment_status',
            'payment_ref',
            'payment_provider',
            'applied_vouchers',
        ] as $column) {
            if (in_array($column, $orderFields, true)) {
                $this->forge->dropColumn('orders', $column);
            }
        }
    }
}
