<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['sku','nombre','descripcion','precio_base','is_activo','imagen_url'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

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
}