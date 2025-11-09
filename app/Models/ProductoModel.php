<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table         = 'productos';
    protected $primaryKey    = 'id';
    protected $useSoftDeletes= true;
    protected $allowedFields = ['sku','nombre','slug','descripcion','precio_base','is_activo','imagen_url'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $beforeInsert  = ['ensureSlug'];
    protected $beforeUpdate  = ['ensureSlugOnNameChange'];

    public function findActivo(int $id): ?array
    {
        return $this->where('id',$id)->where('is_activo',1)->first();
    }

    /** Ajusta el nombre de la columna de inventario si es diferente */
    public function countLowStock(int $threshold = 10): int
    {
        // Cambia 'stock' por tu columna real (p. ej., 'existencias')
        return (int) $this->where('stock <', $threshold)->countAllResults();
    }

    public function getLowStock(int $threshold = 10, int $limit = 10): array
    {
        return $this->select('id, sku, nombre AS name, stock')
            ->where('stock <', $threshold)
            ->orderBy('stock', 'ASC')
            ->limit($limit)
            ->find();
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->where('is_activo', 1)->first();
    }

    /** Callbacks */
    protected function ensureSlug(array $data)
    {
        if (empty($data['data']['slug'])) {
            $data['data']['slug'] = $this->generateUniqueSlug($data['data']['nombre'] ?? $data['data']['sku'] ?? 'producto');
        }
        return $data;
    }

    protected function ensureSlugOnNameChange(array $data)
    {
        if (!isset($data['id'])) return $data;

        // Si viene un slug explícito, respétalo (y será validado por la DB)
        if (!empty($data['data']['slug'])) return $data;

        // Si cambia el nombre, recalcular
        if (isset($data['data']['nombre'])) {
            $current = $this->find(is_array($data['id']) ? $data['id'][0] : $data['id']);
            if ($current && $current['nombre'] !== $data['data']['nombre']) {
                $data['data']['slug'] = $this->generateUniqueSlug($data['data']['nombre'], $current['id']);
            }
        }
        return $data;
    }

    /** Genera slug único (evita colisiones con sufijos -2, -3, ...) */
    public function generateUniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $slug = $this->slugify($text);
        $base = $slug;
        $i = 2;

        while (true) {
            $builder = $this->builder()->select('id')->where('slug', $slug);
            if ($ignoreId) $builder->where('id !=', $ignoreId);
            $exists = $builder->get()->getFirstRow();

            if (!$exists) break;
            $slug = $base.'-'.$i;
            $i++;
        }
        return $slug;
    }

    protected function slugify(string $text): string
    {
        $t = iconv('UTF-8','ASCII//TRANSLIT',$text);
        $t = strtolower($t);
        $t = preg_replace('~[^a-z0-9]+~', '-', $t);
        $t = trim($t, '-');
        return $t ?: 'producto';
    }
}