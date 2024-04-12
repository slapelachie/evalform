<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class SurveySeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $user_data = 
        [
            'username' => $faker->userName(),
            'business_id' => null,
            'first_name' => $faker->firstName(),
            'last_name'=> null,
        ];

        $this->db->table('users')->insert($user_data);
        $user_id = $this->db->insertID();

        $survey_data = [
            'name' => $faker->text(20),
            'description' => $faker->text(200),
            'owner_id' => $user_id,
            'business_id' => null,
            'status' => 'draft',
        ];
        $this->db->table('surveys')->insert($survey_data);
        $survey_id = $this->db->insertID();

        $question_number = 1;
        for ($question_number; $question_number < 5; $question_number++) {
            $question_data = [
                'survey_id' => $survey_id,
                'type' => 'multiple_choice',
                'question_number' => $question_number,
                'question' => $faker->text(50),
            ];
            $this->db->table('questions')->insert($question_data);
            $question_id = $this->db->insertID();

            $question_answer_choice_data = [
                [
                    'question_id' => $question_id,
                    'position' => 0,
                    'answer' => $faker->text(50),
                ],
                [
                    'question_id' => $question_id,
                    'position' => 1,
                    'answer' => $faker->text(50),
                ],
                [
                    'question_id' => $question_id,
                    'position' => 2,
                    'answer' => $faker->text(50),
                ],
                [
                    'question_id' => $question_id,
                    'position' => 3,
                    'answer' => $faker->text(50),
                ],
            ];
            $this->db->table('question_answer_choices')->insertBatch($question_answer_choice_data);
        }

        $question_data = [
            'survey_id' => $survey_id,
            'type' => 'free_text',
            'question_number' => $question_number,
            'question' => $faker->text(100),
        ];
        $this->db->table('questions')->insert($question_data);

    }
}
