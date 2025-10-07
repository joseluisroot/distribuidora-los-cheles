<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMovimientosInventario extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'producto_id' => ['type'=>'INT','unsigned'=>true],
            'tipo'        => ['type'=>'ENUM','constraint'=>['entrada','salida']],
            'cantidad'    => ['type'=>'INT','unsigned'=>true],
            'referencia'  => ['type'=>'VARCHAR','constraint'=>100,'null'=>true],
            'detalle'     => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('producto_id','productos','id','CASCADE','CASCADE');
        $this->forge->createTable('movimientos_inventario');
    }

    public function down()
    {
        $this->forge->dropTable('movimientos_inventario');
    }
}
