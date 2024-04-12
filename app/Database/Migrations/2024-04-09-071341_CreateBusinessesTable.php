<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessesTable extends Migration
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
            'name' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => false,
            ]
        ]);

        $this->forge->addkey('id', true);
        $this->forge->createtable('businesses');
    }

    public function down()
    {
        $this->forge->dropTable('businesses');
    }
}
