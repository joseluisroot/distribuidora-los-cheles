<?php
namespace App\Services;

use App\Models\InventarioModel;
use App\Models\MovimientoInventarioModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class InventarioService
{
    protected InventarioModel $inv;
    protected MovimientoInventarioModel $mov;

    public function __construct()
    {
        $this->inv = new InventarioModel();
        $this->mov = new MovimientoInventarioModel();
    }

    public function ajustar(int $productoId, int $cantidad, string $tipo, ?string $referencia=null, ?string $detalle=null): void
    {
        if (!in_array($tipo, ['entrada','salida'])) {
            throw new DatabaseException('Tipo de movimiento inválido.');
        }

        $row = $this->inv->where('producto_id',$productoId)->first();
        if (!$row) {
            // si no existe registro de inventario, crearlo
            $this->inv->insert(['producto_id'=>$productoId,'stock'=>0]);
            $row = $this->inv->where('producto_id',$productoId)->first();
        }

        $stock = (int)$row['stock'];
        if ($tipo === 'salida' && $cantidad > $stock) {
            throw new DatabaseException('Stock insuficiente para salida.');
        }

        $nuevo = $tipo === 'entrada' ? $stock + $cantidad : $stock - $cantidad;
        $this->inv->update($row['id'], ['stock'=>$nuevo]);

        $this->mov->insert([
            'producto_id' => $productoId,
            'tipo'        => $tipo,
            'cantidad'    => $cantidad,
            'referencia'  => $referencia,
            'detalle'     => $detalle,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
