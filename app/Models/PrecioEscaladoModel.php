<?php

namespace App\Models;

use CodeIgniter\Model;

class PrecioEscaladoModel extends Model
{
    protected $table = 'precios_escalados';
    protected $primaryKey = 'id';
    protected $allowedFields = ['producto_id','min_cantidad','precio'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function getMejorEscala(int $productoId, int $cantidad): ?array
    {
        return $this->where('producto_id',$productoId)
            ->where('min_cantidad <=', $cantidad)
            ->orderBy('min_cantidad','DESC')
            ->first();
    }
}