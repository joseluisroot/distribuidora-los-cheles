<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePreciosEscalados extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'producto_id' => ['type'=>'INT','unsigned'=>true],
            'min_cantidad'=> ['type'=>'INT','unsigned'=>true],
            'precio'      => ['type'=>'DECIMAL','constraint'=>'12,2'],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('producto_id','productos','id','CASCADE','CASCADE');
        $this->forge->createTable('precios_escalados');
    }

    public function down()
    {
        $this->forge->dropTable('precios_escalados');
    }
}
