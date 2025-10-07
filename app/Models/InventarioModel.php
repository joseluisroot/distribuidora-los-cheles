<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table = 'inventarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['producto_id','stock'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function getStockDeProducto(int $productoId): int
    {
        $row = $this->where('producto_id',$productoId)->first();
        return $row ? (int)$row['stock'] : 0;
    }
}