<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductImages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'producto_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'path'        => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],     // ruta imagen grande/original
            'thumb_path'  => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],      // ruta thumb 160x160 (por ej.)
            'alt'         => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'sort_order'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'default'=>0],
            'is_primary'  => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
            'deleted_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['producto_id']);
        $this->forge->addForeignKey('producto_id','productos','id','CASCADE','CASCADE');
        $this->forge->createTable('product_images', true);
    }

    public function down()
    {
        $this->forge->dropTable('product_images', true);
    }
}
