<?php

namespace App\Models;

use CodeIgniter\Model;

class SurveysModel extends Model
{
    protected $table            = 'surveys';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'description', 'owner_id', 'business_id', 'status'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|string|max_length[256]',
        'description' => 'required|string',
        'owner_id' => 'required|int',
        'business_id' => 'permit_empty|int',
        'status' => 'required|string|in_list[draft,published]'
    ];

    protected $validationMessages = [
        'name' => [
            'max_length' => 'Sorry. Your survey title is too long. Consider shortening it!'
        ],
    ];
}
