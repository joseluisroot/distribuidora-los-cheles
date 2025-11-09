<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BackfillProductoSlugs extends Seeder
{
    public function run()
    {
        // App/Database/Seeds/ProductosDemoSeeder.php
// dentro de run(), al insertar el producto:
        $slug = strtolower(trim(preg_replace('~[^a-z0-9]+~', '-', iconv('UTF-8','ASCII//TRANSLIT','Producto Demo')), '-'));

        $this->db->table('productos')->insert([
            'sku'        => 'P-002',
            'nombre'     => 'Producto Demo 2',
            'slug'       => $slug,
            'descripcion'=> 'Ejemplo de producto inicial producto 2',
            'precio_base'=> 10.00,
            'is_activo'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

    }
}
