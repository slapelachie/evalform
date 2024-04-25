<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionsModel extends Model
{
    protected $table            = 'questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['survey_id', 'type', 'question_number', 'question'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'survey_id' => 'required|integer',
        'type' => 'required|string|in_list[multiple_choice,free_text]',
        'question_number' => 'required|integer',
        'question' => 'required|string'
    ];
}
