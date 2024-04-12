<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionAnswerChoicesModel extends Model
{
    protected $table            = 'question_answer_choices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['question_id', 'answer'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'question_id' => 'required|integer',
        'position' => 'required|integer',
        'answer' => 'required|string|max_length[256]',
    ];
}
