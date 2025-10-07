<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductosDemoSeeder extends Seeder
{
    public function run()
    {
        // Producto
        $this->db->table('productos')->insert([
            'sku'        => 'P-001',
            'nombre'     => 'Producto Demo',
            'descripcion'=> 'Ejemplo de producto inicial',
            'precio_base'=> 10.00,
            'is_activo'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $productoId = $this->db->insertID();

        // Inventario inicial
        $this->db->table('inventarios')->insert([
            'producto_id'=>$productoId,
            'stock'=>100,
            'created_at'=>date('Y-m-d H:i:s'),
        ]);

        // Escalas de precios
        $this->db->table('precios_escalados')->insertBatch([
            ['producto_id'=>$productoId,'min_cantidad'=>1,'precio'=>10,'created_at'=>date('Y-m-d H:i:s')],
            ['producto_id'=>$productoId,'min_cantidad'=>3,'precio'=>9,'created_at'=>date('Y-m-d H:i:s')],
            ['producto_id'=>$productoId,'min_cantidad'=>10,'precio'=>8,'created_at'=>date('Y-m-d H:i:s')],
        ]);
    }
}
