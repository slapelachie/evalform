<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'varchar',
                'constraint' => 63,
                'null' => false,
            ],
            'is_admin' => [
                'type' => 'int',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
            ],
            'business_id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'first_name' => [
                'type' => 'varchar',
                'constraint' => 63,
                'null' => false,
            ],
            'last_name' => [
                'type' => 'varchar',
                'constraint' => 63,
                'null' => true,
            ]
        ]);

        $this->forge->addkey('id', true);

        // If the business gets deleted, set the business_id to NULL
        $this->forge->addForeignKey('business_id', 'businesses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createtable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
