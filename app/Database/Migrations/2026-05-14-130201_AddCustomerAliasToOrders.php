<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerAliasToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'customer_alias' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'after'      => 'customer_name'
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'customer_alias'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['customer_alias', 'user_id']);
    }
}
