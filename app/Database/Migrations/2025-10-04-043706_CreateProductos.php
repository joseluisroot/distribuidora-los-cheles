<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'sku'           => ['type'=>'VARCHAR','constraint'=>50,'unique'=>true],
            'nombre'        => ['type'=>'VARCHAR','constraint'=>150],
            'descripcion'   => ['type'=>'TEXT','null'=>true],
            'precio_base'   => ['type'=>'DECIMAL','constraint'=>'12,2','default'=>0],
            'is_activo'     => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'created_at'    => ['type'=>'DATETIME','null'=>true],
            'updated_at'    => ['type'=>'DATETIME','null'=>true],
            'deleted_at'    => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('productos');
    }

    public function down()
    {
        $this->forge->dropTable('productos');
    }
}
