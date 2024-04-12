<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $business_data = [
            [
                'name' => 'Foo Bar Inc',
            ],
        ];
        $this->db->table('businesses')->insertBatch($business_data);
        $business_id = $this->db->insertID();

        $user_data = [
            [
                'username' => 'lorem',
                'business_id' => null,
                'first_name' => 'Lorem',
                'last_name'=> null,
            ],
            [
                'username' => 'ipsum',
                'business_id' => $business_id,
                'first_name' => 'Ipsum',
                'last_name'=> null,
            ],
        ];

        $this->db->table('users')->insertBatch($user_data);
    }
}
