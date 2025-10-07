<?php
namespace App\Services;

use App\Models\PedidoModel;
use App\Models\PedidoDetalleModel;
use App\Models\PedidoEstadoHistorialModel;
use App\Models\ProductoModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PedidoService
{
    protected PedidoModel $pedidos;
    protected PedidoDetalleModel $detalles;
    protected PedidoEstadoHistorialModel $historial;
    protected InventarioService $inventario;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->pedidos   = new PedidoModel();
        $this->detalles  = new PedidoDetalleModel();
        $this->historial = new PedidoEstadoHistorialModel();
        $this->inventario= new InventarioService();
        $this->db        = db_connect();
    }

    /** Crea un pedido vacío en estado 'ingresado' */
    public function crearPedido(int $clienteId, ?string $observaciones=null): array
    {
        $id = $this->pedidos->insert([
            'cliente_id'   => $clienteId,
            'estado'       => 'ingresado',
            'total'        => 0,
            'observaciones'=> $observaciones,
        ], true);

        // auditar estado inicial
        $this->historial->insert([
            'pedido_id'   => $id,
            'estado'      => 'ingresado',
            'cambiado_por'=> $clienteId,
            'nota'        => 'Creación del pedido',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->pedidos->find($id);
    }

    /** Agrega un ítem y recalcula total; aplica precio por escala a la cantidad actual del ítem */
    public function agregarItem(int $pedidoId, int $productoId, int $cantidad): void
    {
        if ($cantidad < 1) throw new DatabaseException('Cantidad inválida.');

        $pedido = $this->pedidos->find($pedidoId);
        if (!$pedido || $pedido['estado'] !== 'ingresado') {
            throw new DatabaseException('Solo se agregan ítems con pedido en estado ingresado.');
        }

        // precio por escala para ESA cantidad
        $precioUnit = (float)precio_por_cantidad($productoId, $cantidad);
        $subtotal   = round($precioUnit * $cantidad, 2, PHP_ROUND_HALF_UP);

        // insertar detalle
        $this->detalles->insert([
            'pedido_id'   => $pedidoId,
            'producto_id' => $productoId,
            'cantidad'    => $cantidad,
            'precio_unit' => $precioUnit,
            'subtotal'    => $subtotal,
        ]);

        // actualizar total
        $nuevoTotal = $this->detalles->totalizarPedido($pedidoId);
        $this->pedidos->update($pedidoId, ['total'=>$nuevoTotal]);
    }

    /** Confirmar: descuenta inventario y marca 'preparando' */
    public function confirmar(int $pedidoId, int $usuarioId): void
    {
        $this->db->transStart();

        $pedido = $this->pedidos->lockForUpdate()->find($pedidoId);
        if (!$pedido || $pedido['estado'] !== 'ingresado') {
            $this->db->transRollback();
            throw new DatabaseException('Pedido no válido para confirmar.');
        }

        $items = $this->detalles->where('pedido_id',$pedidoId)->findAll();
        if (!$items) {
            $this->db->transRollback();
            throw new DatabaseException('El pedido no tiene ítems.');
        }

        // Descontar inventario
        foreach ($items as $it) {
            $this->inventario->ajustar(
                (int)$it['producto_id'],
                (int)$it['cantidad'],
                'salida',
                'PED-'.$pedidoId,
                'Salida por confirmación de pedido'
            );
        }

        // cambiar estado a 'preparando'
        $this->pedidos->update($pedidoId, ['estado'=>'preparando']);

        $this->historial->insert([
            'pedido_id'   => $pedidoId,
            'estado'      => 'preparando',
            'cambiado_por'=> $usuarioId,
            'nota'        => 'Confirmación y reserva de inventario',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            throw new DatabaseException('Error al confirmar el pedido.');
        }
    }

    /** Marcar procesado (entregado/facturado) */
    public function procesar(int $pedidoId, int $usuarioId, ?string $nota=null): void
    {
        $pedido = $this->pedidos->find($pedidoId);
        if (!$pedido || !in_array($pedido['estado'], ['preparando'])) {
            throw new DatabaseException('Pedido no válido para procesar.');
        }

        $this->pedidos->update($pedidoId, ['estado'=>'procesado']);
        $this->historial->insert([
            'pedido_id'   => $pedidoId,
            'estado'      => 'procesado',
            'cambiado_por'=> $usuarioId,
            'nota'        => $nota ?: 'Pedido procesado',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
