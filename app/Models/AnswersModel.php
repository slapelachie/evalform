<?php

namespace App\Models;

use CodeIgniter\Model;

class AnswersModel extends Model
{
    protected $table            = 'answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['question_id', 'position', 'answer'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'question_id' => 'required|integer',
        'position' => 'required|integer',
        'answer' => 'required|string|max_length[256]',
    ];
}
