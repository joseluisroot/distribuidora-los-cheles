<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugToProductos extends Migration
{
    public function up()
    {
        // Agregar campo slug
        $this->forge->addColumn('productos', [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 180,
                'null'       => true, // temporal mientras llenamos valores
                'after'      => 'nombre',
            ],
        ]);

        // El índice único se crea en AddSlugUniqueIndex.
    }

    public function down()
    {
        $this->forge->dropColumn('productos', 'slug');
    }
}
