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

        // Índice único (lo aplicaremos después de poblar los slugs)
        $this->forge->addKey('slug', true); // <- no funciona aquí, se hace con query
    }

    public function down()
    {
        $this->forge->dropColumn('productos', 'slug');
    }
}
