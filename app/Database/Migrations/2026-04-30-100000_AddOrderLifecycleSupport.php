<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOrderLifecycleSupport extends Migration
{
    public function up()
    {
        $orderFields = $this->db->getFieldNames('orders');

        $addOrderColumn = function (string $name, array $definition) use (&$orderFields): void {
            if (! in_array($name, $orderFields, true)) {
                $this->forge->addColumn('orders', [$name => $definition]);
                $orderFields[] = $name;
            }
        };

        $addOrderColumn('tracking_number', [
            'type' => 'VARCHAR',
            'constraint' => 80,
            'null' => true,
            'after' => 'applied_vouchers',
        ]);
        $addOrderColumn('courier_name', [
            'type' => 'VARCHAR',
            'constraint' => 80,
            'null' => true,
            'after' => 'tracking_number',
        ]);
        $addOrderColumn('shipped_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'courier_name',
        ]);
        $addOrderColumn('delivered_at', [
            'type' => 'DATETIME',
            'null' => true,
            'after' => 'shipped_at',
        ]);
        $addOrderColumn('cancel_reason', [
            'type' => 'TEXT',
            'null' => true,
            'after' => 'delivered_at',
        ]);

        if (! $this->db->tableExists('order_reviews')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'order_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'customer_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'rating' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                ],
                'comment' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'media_paths' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('order_id');
            $this->forge->createTable('order_reviews', true);
        }

        if (! $this->db->tableExists('refund_requests')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'order_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'customer_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'Pending',
                ],
                'evidence_paths' => [
                    'type' => 'TEXT',
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
            $this->forge->addKey('order_id');
            $this->forge->createTable('refund_requests', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('refund_requests')) {
            $this->forge->dropTable('refund_requests', true);
        }
        if ($this->db->tableExists('order_reviews')) {
            $this->forge->dropTable('order_reviews', true);
        }

        $orderFields = $this->db->getFieldNames('orders');
        foreach (['tracking_number', 'courier_name', 'shipped_at', 'delivered_at', 'cancel_reason'] as $column) {
            if (in_array($column, $orderFields, true)) {
                $this->forge->dropColumn('orders', $column);
            }
        }
    }
}
