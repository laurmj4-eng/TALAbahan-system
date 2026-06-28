<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeveloperRoleSupport extends Migration
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

        // Create the developer user
        $this->db->table('users')->insert([
            'username' => 'mjlaurrito',
            'email'    => 'dev@talabahan.app',
            'password' => password_hash('mjlauritodevmode', PASSWORD_DEFAULT),
            'role'     => 'developer',
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('fcm_device_tokens', 'app_version');
        $this->forge->dropColumn('fcm_device_tokens', 'last_connected');

        // Remove the developer user (safe rollback)
        $this->db->table('users')->where('role', 'developer')->delete();
    }
}
