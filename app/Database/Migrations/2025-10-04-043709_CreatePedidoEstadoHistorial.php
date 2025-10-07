<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidoEstadoHistorial extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'pedido_id'  => ['type'=>'INT','unsigned'=>true],
            'estado'     => ['type'=>'ENUM','constraint'=>['ingresado','preparando','procesado']],
            'cambiado_por'=>['type'=>'INT','unsigned'=>true],
            'nota'       => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pedido_id','pedidos','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('cambiado_por','users','id','RESTRICT','RESTRICT');
        $this->forge->createTable('pedido_estado_historial');
    }

    public function down()
    {
        $this->forge->dropTable('pedido_estado_historial');
    }
}
