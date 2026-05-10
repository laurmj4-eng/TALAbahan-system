<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FinalizeActivityLogsTable extends Migration
{
    public function up()
    {
        // Drop existing table if it exists to start fresh with professional schema
        $this->forge->dropTable('activity_logs', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'user_identity' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'default'    => 'Guest',
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'device' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '45',
            ],
            'status_code' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}
