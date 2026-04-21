<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesHistoryTable extends Migration
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
            'transaction_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'items_summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'total_amount' => [
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
        $this->forge->createTable('sales_history');
    }

    public function down()
    {
        $this->forge->dropTable('sales_history');
    }
}
