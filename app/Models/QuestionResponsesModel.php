<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionResponsesModel extends Model
{
    protected $table            = 'question_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['question_id', 'survey_response_id', 'answer_id', 'answer_text'];

    protected $useTimestamps = false;

    /* Validation */
    protected $validationRules = [
        'question_id' => 'required|integer',
        'survey_response_id' => 'required|integer',
        'answer_id' => 'permit_empty|integer',
        'answer_text' => 'permit_empty|string',
    ];
}
