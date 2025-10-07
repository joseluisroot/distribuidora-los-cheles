<?php

namespace App\Models;

use CodeIgniter\Model;

class MovimientoInventarioModel extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id';
    protected $allowedFields = ['producto_id','tipo','cantidad','referencia','detalle','created_at'];
    public $useTimestamps = false; // created_at lo seteamos nosotros
    protected $returnType = 'array';
}