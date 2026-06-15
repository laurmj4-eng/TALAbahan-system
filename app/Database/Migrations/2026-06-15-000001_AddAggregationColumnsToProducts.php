<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAggregationColumnsToProducts extends Migration
{
    public function up()
    {
        $fields = [
            'real_sold_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'real_rating' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,1',
                'default'    => null,
                'null'       => true,
            ],
        ];

        // Check if table exists first
        if ($this->db->tableExists('products')) {
            $this->forge->addColumn('products', $fields);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('products')) {
            if ($this->db->fieldExists('real_sold_count', 'products')) {
                $this->forge->dropColumn('products', 'real_sold_count');
            }
            if ($this->db->fieldExists('real_rating', 'products')) {
                $this->forge->dropColumn('products', 'real_rating');
            }
        }
    }
}
