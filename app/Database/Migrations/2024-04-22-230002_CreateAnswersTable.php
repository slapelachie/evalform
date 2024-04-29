<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnswersTable extends Migration
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
            'position' => [
                'type' => 'INT',
                'constraint' => 4,
                'unsigned' => true,
            ],
            'answer' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
        ]);

        $this->forge->addkey('id', true);

        $this->forge->addForeignKey('question_id', 'questions', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createtable('question_answer_choices');
    }

    public function down()
    {
        $this->forge->dropTable('question_answer_choices');
    }
}
