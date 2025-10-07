<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['sku','nombre','descripcion','precio_base','is_activo'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function findActivo(int $id): ?array
    {
        return $this->where('id',$id)->where('is_activo',1)->first();
    }
}