<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCostPriceToOrderItems extends Migration
{
    public function up()
    {
        $fields = $this->db->getFieldNames('order_items');
        if (!in_array('cost_price', $fields, true)) {
            $this->forge->addColumn('order_items', [
                'cost_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '12,2',
                    'default' => 0.00,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        $fields = $this->db->getFieldNames('order_items');
        if (in_array('cost_price', $fields, true)) {
            $this->forge->dropColumn('order_items', 'cost_price');
        }
    }
}
