<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UsersModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();
    }
}
