<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAppVersionAndLastConnected extends Migration
{
    public function up()
    {
        $this->forge->addColumn('fcm_device_tokens', [
            'app_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'device_model',
            ],
            'last_connected' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'app_version',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('fcm_device_tokens', 'app_version');
        $this->forge->dropColumn('fcm_device_tokens', 'last_connected');
    }
}
