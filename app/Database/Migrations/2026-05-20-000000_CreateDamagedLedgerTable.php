<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDamagedLedgerTable extends Migration
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
            'order_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'product_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'product_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'total_loss' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'recorded_by' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('damaged_ledger');
    }

    public function down()
    {
        $this->forge->dropTable('damaged_ledger');
    }
}
