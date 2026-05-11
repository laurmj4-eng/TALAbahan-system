<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsAvailableToProducts extends Migration
{
    public function up()
    {
        $this->forge->addColumn('products', [
            'is_available' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'after'      => 'image',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'is_available');
    }
}
