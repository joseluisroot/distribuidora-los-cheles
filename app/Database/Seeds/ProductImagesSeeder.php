<?php

namespace App\Database\Seeds;

use App\Models\ProductoModel;
use CodeIgniter\Database\Seeder;

class ProductImagesSeeder extends Seeder
{
    public function run()
    {
        $db       = \Config\Database::connect();
        $builder  = $db->table('product_images');
        $productos = (new ProductoModel())->select('id, nombre')->where('is_activo', 1)->findAll();

        if (empty($productos)) {
            echo "No hay productos activos para generar imágenes.\n";
            return;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($productos as $p) {
            $pid   = (int) $p['id'];
            $name  = (string) ($p['nombre'] ?? 'Producto');
            // entre 1 y 4 imágenes por producto
            $count = rand(1, 4);

            for ($i = 1; $i <= $count; $i++) {
                // Usamos seed para que sean “aleatorias” pero reproducibles
                $seed      = "cheles-{$pid}-{$i}-" . rand(1000, 9999);
                $fullUrl   = "https://picsum.photos/seed/{$seed}/800/600";
                $thumbUrl  = "https://picsum.photos/seed/{$seed}/160/160";

                $builder->insert([
                    'producto_id' => $pid,
                    // Guardamos URL absoluta; ver ajuste en la vista más abajo
                    'path'        => $fullUrl,
                    'thumb_path'  => $thumbUrl,
                    'alt'         => "{$name} - imagen {$i}",
                    'sort_order'  => $i - 1,
                    'is_primary'  => $i === 1 ? 1 : 0,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        echo "ProductImagesSeeder: imágenes de placeholder generadas.\n";
    }
}
