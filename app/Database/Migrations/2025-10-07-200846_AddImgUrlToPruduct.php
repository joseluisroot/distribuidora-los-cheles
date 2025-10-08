<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImgUrlToPruduct extends Migration
{
    public function up()
    {
        $this->forge->addColumn('productos', [
            'imagen_url' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true, 'after'=>'descripcion'],
        ]);
    }
    public function down()
    {
        $this->forge->dropColumn('productos','imagen_url');
    }
}
