<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['username', 'business_id', 'first_name', 'last_name'];

    protected $useTimestamps = false;

    protected $validationRules = [
        'username' => 'required|string|is_unique|max_length[64]',
        'business_id' => 'permit_empty|int',
        'first_name' => 'required|string|max_length[64]',
        'last_name' => 'permit_empty|string|max_length[64]',
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique' => 'Sorry. This username has already been taken. Please choose a new username.',
            'max_length' => 'Sorry. This username is too long. Consider choosing a shorter username.',
        ],
    ];
}
