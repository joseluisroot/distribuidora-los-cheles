<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'producto_id' => ['type'=>'INT','unsigned'=>true],
            'stock'       => ['type'=>'INT','default'=>0],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('producto_id','productos','id','CASCADE','CASCADE');
        $this->forge->createTable('inventarios');
    }

    public function down()
    {
        $this->forge->dropTable('inventarios');
    }
}
