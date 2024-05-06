<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class MultipleSurveySeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();

        $surveyCount = 150;
        $userId = 1;

        for ($i = 0; $i < $surveyCount; $i++) {
            $surveyData = [
                'name' => $faker->text(20),
                'description' => $faker->text(200),
                'owner_id' => $userId,
                'status' => 'draft',
            ];
            $this->db->table('surveys')->insert($surveyData);
            $surveyId = $this->db->insertID();

            $questionNumber = 1;
            for ($questionNumber; $questionNumber < 5; $questionNumber++) {
                $questionData = [
                    'survey_id' => $surveyId,
                    'type' => 'multiple_choice',
                    'question_number' => $questionNumber,
                    'question' => $faker->text(50),
                ];
                $this->db->table('questions')->insert($questionData);
                $questionId = $this->db->insertID();

                $answersData = [
                    [
                        'question_id' => $questionId,
                        'position' => 0,
                        'answer' => $faker->text(50),
                    ],
                    [
                        'question_id' => $questionId,
                        'position' => 1,
                        'answer' => $faker->text(50),
                    ],
                    [
                        'question_id' => $questionId,
                        'position' => 2,
                        'answer' => $faker->text(50),
                    ],
                    [
                        'question_id' => $questionId,
                        'position' => 3,
                        'answer' => $faker->text(50),
                    ],
                ];
                $this->db->table('answers')->insertBatch($answersData);
            }

            $questionData = [
                'survey_id' => $surveyId,
                'type' => 'free_text',
                'question_number' => $questionNumber,
                'question' => $faker->text(100),
            ];
            $this->db->table('questions')->insert($questionData);
        }
    }
}
