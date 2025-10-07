<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $role = $this->db->table('roles')->where('name','admin')->get()->getRow();
        $this->db->table('users')->insert([
            'role_id'    => $role->id,
            'name'       => 'Administrador',
            'email'      => 'admin@demo.test',
            'password'   => password_hash('Admin123!', PASSWORD_DEFAULT),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
