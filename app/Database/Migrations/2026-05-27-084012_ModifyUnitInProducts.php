<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyUnitInProducts extends Migration
{
    public function up()
    {
        $fields = [
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'kg',
                'null'       => true
            ],
        ];
        $this->forge->modifyColumn('products', $fields);
    }

    public function down()
    {
        $fields = [
            'unit' => [
                'type'       => 'ENUM',
                'constraint' => ['kg', 'piece', 'batch'],
                'default'    => 'kg',
                'null'       => true
            ],
        ];
        $this->forge->modifyColumn('products', $fields);
    }
}
