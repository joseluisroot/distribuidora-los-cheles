<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoDetalleModel extends Model
{
    protected $table = 'pedido_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pedido_id','producto_id','cantidad','precio_unit','subtotal'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function totalizarPedido(int $pedidoId): float
    {
        $rows = $this->select('subtotal')->where('pedido_id',$pedidoId)->findAll();
        $total = 0.0;
        foreach ($rows as $r) $total += (float)$r['subtotal'];
        return round($total, 2, PHP_ROUND_HALF_UP);
    }
}