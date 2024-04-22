<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\Forge;

class AddAttributesToUsers extends Migration
{
     /**
     * @var string[]
     */
    private array $tables;

    public function __construct(?Forge $forge = null)
    {
        parent::__construct($forge);

        /** @var \Config\Auth $authConfig */
        $authConfig   = config('Auth');
        $this->tables = $authConfig->tables;
    }

    public function up()
    {
        $fields = [
            'business_id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ];

        $this->forge->addForeignKey('business_id', 'businesses', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addColumn($this->tables['users'], $fields);
    }

    public function down()
    {
        $fields = [
            'business_id',
        ];
        $this->forge->dropColumn($this->tables['users'], $fields);
    }
}
