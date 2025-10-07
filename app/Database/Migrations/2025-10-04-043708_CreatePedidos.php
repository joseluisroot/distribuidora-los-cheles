<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'cliente_id'  => ['type'=>'INT','unsigned'=>true],
            'estado'      => ['type'=>'ENUM','constraint'=>['ingresado','preparando','procesado'],'default'=>'ingresado'],
            'total'       => ['type'=>'DECIMAL','constraint'=>'14,2','default'=>0],
            'observaciones'=>['type'=>'TEXT','null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
            'deleted_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('cliente_id','users','id','RESTRICT','RESTRICT');
        $this->forge->createTable('pedidos');
    }

    public function down()
    {
        $this->forge->dropTable('pedidos');
    }
}
