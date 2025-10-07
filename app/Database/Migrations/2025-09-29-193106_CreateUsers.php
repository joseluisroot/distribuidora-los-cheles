<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'role_id'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'name'        => ['type'=>'VARCHAR','constraint'=>100],
            'email'       => ['type'=>'VARCHAR','constraint'=>150,'unique'=>true],
            'password'    => ['type'=>'VARCHAR','constraint'=>255],
            'is_active'   => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
            'deleted_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
