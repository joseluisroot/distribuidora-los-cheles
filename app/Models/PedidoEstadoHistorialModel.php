<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoEstadoHistorialModel extends Model
{
    protected $table = 'pedido_estado_historial';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pedido_id','estado','cambiado_por','nota','created_at'];
    public $useTimestamps = false;
    protected $returnType = 'array';
}