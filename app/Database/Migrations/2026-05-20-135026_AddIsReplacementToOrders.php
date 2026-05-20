<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsReplacementToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'is_replacement' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
            'replaces_order_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['is_replacement', 'replaces_order_id']);
    }
}
