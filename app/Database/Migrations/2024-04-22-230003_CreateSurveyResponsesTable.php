<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSurveyResponsesTable extends Migration
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
            'survey_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'submit_time' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addkey('id', true);

        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createtable('survey_responses');
    }

    public function down()
    {
        $this->forge->dropTable('survey_responses');
    }
}
