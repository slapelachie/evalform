<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuestionResponsesTable extends Migration
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
            'question_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'answer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'answer_text' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addkey('id', true);

        $this->forge->addForeignKey('question_id', 'questions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('answer_id', 'question_answer_choices', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createtable('question_responses');
    }

    public function down()
    {
        $this->forge->dropTable('question_responses');
    }
}
