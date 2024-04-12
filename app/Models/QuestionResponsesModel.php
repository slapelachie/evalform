<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionResponsesModel extends Model
{
    protected $table            = 'questionresponses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['question_id', 'answer_id', 'answer_text'];

    protected $useTimestamps = false;

    /* Validation */
    protected $validationRules = [
        'question_id' => 'required|integer',
        'answer_id' => 'permit_empty|integer',
        'answer_text' => 'permit_empty|string',
        'answer_validation' => 'check_answer_presence'
    ];

    // Check to make sure at least one of answer_{id,text} are filled out
    private function check_answer_presence($str, string $fields, array $data)
    {
        if (empty($data['answer_id']) && empty($data['answer_text'])) {
            return false;
        }

        return true;
    }

    /* Callbacks */
    // Check to make sure that the data is correctly validated
    protected $beforeInsert = ['validateAnswers'];
    protected $beforeUpdate = ['validateAnswers'];

    protected function validateAnswers(array $data)
    {
        $data['data']['answer_validation'] = true;
        return $data;
    }
}
