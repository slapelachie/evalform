<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $user_data = [
            'username' => 'admin',
            ''
        ];

        $this->db->table('users')->insertBatch($user_data);
    }
}
