<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSurveysTable extends Migration
{
    public function up()
    {
        $this->forge->addfield([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'business_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'published'],
                'default' => 'draft',
            ],
        ]);

        $this->forge->addkey('id', true);

        $this->forge->addForeignKey('owner_id', 'users', 'id', 'CASCADE', 'CASCADE');
        // Is set if a business has ownership over the survey
        $this->forge->addForeignKey('business_id', 'businesses', 'id', 'CASCADE', 'SET NULL');

        $this->forge->createtable('surveys');
    }

    public function down()
    {
        $this->forge->dropTable('surveys');
    }
}
