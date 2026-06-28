<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTrustedAdminDeviceFlag extends Migration
{
    public function up()
    {
        $this->forge->addColumn('fcm_device_tokens', [
            'is_trusted_admin_device' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_active',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('fcm_device_tokens', 'is_trusted_admin_device');
    }
}
