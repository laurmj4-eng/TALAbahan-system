<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastActiveToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'last_active' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'last_active');
    }
}
