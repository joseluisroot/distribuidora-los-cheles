<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('roles')->insertBatch([
            ['name' => 'admin',   'created_at'=>date('Y-m-d H:i:s')],
            ['name' => 'cliente', 'created_at'=>date('Y-m-d H:i:s')],
        ]);
    }
}
