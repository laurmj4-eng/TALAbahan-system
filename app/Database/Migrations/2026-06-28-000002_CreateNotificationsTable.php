<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'type'            => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'order_update'],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'body'            => ['type' => 'TEXT', 'null' => true],
            'data'            => ['type' => 'TEXT', 'null' => true],
            'is_read'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'read_at'         => ['type' => 'DATETIME', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_read');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
