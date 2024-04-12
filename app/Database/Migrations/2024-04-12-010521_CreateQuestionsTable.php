<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionsTable extends Migration
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
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['multiple_choice', 'free_text'],
                'default' => 'free_text',
            ],
            'question_number' => [
                'type' => 'INT',
                'constraint' => 4,
                'unsigned' => true,
            ],
            'question' => [
                'type' => 'TEXT',
                'null' => false,
            ],
        ]);

        $this->forge->addkey('id', true);

        $this->forge->addForeignKey('survey_id', 'surveys', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createtable('questions');
    }

    public function down()
    {
        $this->forge->dropTable('questions');
    }
}
