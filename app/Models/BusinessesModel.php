<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessesModel extends Model
{
    protected $table            = 'businesses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['name'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'name' => 'required|string|max_lenth[256]',
    ];

    protected $validationMessages = [
        'name' => [
            'max_length' => 'Sorry. This name is too long. Consider shortening it!',
        ],
    ];
}
