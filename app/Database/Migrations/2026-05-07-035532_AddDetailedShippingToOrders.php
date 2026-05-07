<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailedShippingToOrders extends Migration
{
    public function up()
    {
        $this->forge->addColumn('orders', [
            'shipping_city' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'shipping_barangay',
            ],
            'shipping_street' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'shipping_city',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', ['shipping_city', 'shipping_street']);
    }
}
