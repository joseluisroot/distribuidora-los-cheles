<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePedidoDetalle extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'pedido_id'    => ['type'=>'INT','unsigned'=>true],
            'producto_id'  => ['type'=>'INT','unsigned'=>true],
            'cantidad'     => ['type'=>'INT','unsigned'=>true],
            'precio_unit'  => ['type'=>'DECIMAL','constraint'=>'12,2'],
            'subtotal'     => ['type'=>'DECIMAL','constraint'=>'14,2'],
            'created_at'   => ['type'=>'DATETIME','null'=>true],
            'updated_at'   => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pedido_id','pedidos','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('producto_id','productos','id','CASCADE','CASCADE');
        $this->forge->createTable('pedido_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('pedido_detalle');
    }
}
