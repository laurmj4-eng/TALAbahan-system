<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFcmDeviceTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'token'          => ['type' => 'TEXT', 'null' => false],
            'platform'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'android'],
            'device_model'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('token', false, true);
        $this->forge->createTable('fcm_device_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('fcm_device_tokens');
    }
}
