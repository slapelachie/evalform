<?php

namespace App\Models;

use CodeIgniter\Model;

class SurveyResponsesModel extends Model
{
    protected $table            = 'survey_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['survey_id', 'submit_time'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'submit_time';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules = [
        'survey_id' => 'required|integer',
    ];
}
