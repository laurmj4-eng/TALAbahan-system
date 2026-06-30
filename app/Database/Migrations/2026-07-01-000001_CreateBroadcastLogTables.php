<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBroadcastLogTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'default'    => 'System Broadcast',
            ],
            'body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'target' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'all',
            ],
            'total_devices' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'sent_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'failed_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'delivered_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addField('updated_at DATETIME NULL DEFAULT NULL');
        $this->forge->createTable('broadcast_logs');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'broadcast_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'device_model' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'sent',
            ],
            'fcm_error' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('broadcast_id');
        $this->forge->addKey('token');
        $this->forge->addField('updated_at DATETIME NULL DEFAULT NULL');
        $this->forge->addForeignKey('broadcast_id', 'broadcast_logs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('broadcast_receipts');
    }

    public function down()
    {
        $this->forge->dropTable('broadcast_receipts');
        $this->forge->dropTable('broadcast_logs');
    }
}
