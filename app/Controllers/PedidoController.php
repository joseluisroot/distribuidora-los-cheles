<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PedidoDetalleModel;
use App\Models\PedidoEstadoHistorialModel;
use App\Models\PedidoModel;
use App\Services\PedidoService;

class PedidoController extends BaseController
{

    protected $pedidoModel;
    protected $detalleModel;
    protected $historialModel;

    public function __construct()
    {
        $this->pedidoModel    = new PedidoModel();
        $this->detalleModel   = new PedidoDetalleModel();
        $this->historialModel = new PedidoEstadoHistorialModel();
    }

    // Listar pedidos (admin ve todos, cliente solo los suyos)
    public function index()
    {
        $user = session('user');

        $builder = $this->pedidoModel->select('pedidos.*, users.name as cliente')
            ->join('users', 'users.id = pedidos.cliente_id');

        if ($user['role'] === 'cliente') {
            $builder->where('cliente_id', $user['id']);
        }

        $pedidos = $builder->orderBy('pedidos.created_at','DESC')->findAll();

        return view('pedidos/index', [
            'pedidos' => $pedidos,
            'title'   => 'Pedidos'
        ]);
    }

    // Ver detalle de un pedido
    public function ver($id)
    {
        $pedido = $this->pedidoModel->select('pedidos.*, users.name as cliente')
            ->join('users','users.id = pedidos.cliente_id')
            ->where('pedidos.id', $id)
            ->first();

        $detalles = $this->detalleModel->select('pedido_detalle.*, productos.nombre')
            ->join('productos','productos.id = pedido_detalle.producto_id')
            ->where('pedido_id',$id)->findAll();

        $historial = $this->historialModel->select('pedido_estado_historial.*, users.name as usuario')
            ->join('users','users.id = pedido_estado_historial.cambiado_por')
            ->where('pedido_id',$id)->orderBy('created_at','ASC')->findAll();

        return view('pedidos/ver', [
            'pedido'=>$pedido,
            'detalles'=>$detalles,
            'historial'=>$historial,
            'title'=>'Detalle de Pedido'
        ]);
    }

    public function reporte()
    {
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');
        $estado = $this->request->getGet('estado'); // null | ingresado | preparando | procesado

        $builder = $this->pedidoModel
            ->select('pedidos.*, users.name as cliente')
            ->join('users','users.id = pedidos.cliente_id')
            ->orderBy('pedidos.created_at','DESC');

        if ($from)  $builder->where('DATE(pedidos.created_at) >=', $from);
        if ($to)    $builder->where('DATE(pedidos.created_at) <=', $to);
        if ($estado && in_array($estado, ['ingresado','preparando','procesado'])) {
            $builder->where('pedidos.estado', $estado);
        }

        $pedidos = $builder->findAll();

        // Totales
        $cantidad = count($pedidos);
        $importe  = 0.0;
        foreach ($pedidos as $p) $importe += (float)$p['total'];

        return view('pedidos/reporte', [
            'pedidos'  => $pedidos,
            'from'     => $from,
            'to'       => $to,
            'estado'   => $estado,
            'cantidad' => $cantidad,
            'importe'  => round($importe,2, PHP_ROUND_HALF_UP),
            'title'    => 'Reporte de Pedidos'
        ]);
    }

    public function exportReporte()
    {
        $from   = $this->request->getGet('from');
        $to     = $this->request->getGet('to');
        $estado = $this->request->getGet('estado');

        $builder = $this->pedidoModel
            ->select('pedidos.*, users.name as cliente')
            ->join('users','users.id = pedidos.cliente_id')
            ->orderBy('pedidos.created_at','DESC');

        if ($from)  $builder->where('DATE(pedidos.created_at) >=', $from);
        if ($to)    $builder->where('DATE(pedidos.created_at) <=', $to);
        if ($estado && in_array($estado, ['ingresado','preparando','procesado'])) {
            $builder->where('pedidos.estado', $estado);
        }

        $rows = $builder->findAll();

        // CSV
        $filename = 'reporte_pedidos_'.date('Ymd_His').'.csv';
        $fh = fopen('php://temp','w');

        fputcsv($fh, ['Reporte de Pedidos']);
        fputcsv($fh, ['Rango', ($from ?: '-') . ' a ' . ($to ?: '-')]);
        fputcsv($fh, ['Estado', $estado ?: 'Todos']);
        fputcsv($fh, []); // blanco
        fputcsv($fh, ['ID','Fecha','Cliente','Estado','Total']);

        $total = 0.0;
        foreach ($rows as $r) {
            $total += (float)$r['total'];
            fputcsv($fh, [
                $r['id'],
                date('Y-m-d H:i', strtotime($r['created_at'])),
                $r['cliente'],
                $r['estado'],
                number_format((float)$r['total'], 2, '.', ''),
            ]);
        }

        fputcsv($fh, []); // blanco
        fputcsv($fh, ['Cantidad', count($rows)]);
        fputcsv($fh, ['Importe Total', number_format($total, 2, '.', '')]);

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type','text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition','attachment; filename="'.$filename.'"')
            ->setBody($csv);
    }

    public function cambiarEstado($id)
    {
        $user = session('user');
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            return redirect()->back()->with('error','No autorizado.');
        }

        $pedido = $this->pedidoModel->find($id);
        if (!$pedido) {
            return redirect()->back()->with('error','Pedido no encontrado.');
        }

        $destino = $this->request->getPost('estado_destino'); // ingresado | preparando | procesado
        $nota    = trim((string)$this->request->getPost('nota'));

        $svc = new PedidoService();

        try {
            if ($destino === 'preparando') {
                if ($pedido['estado'] === 'ingresado') {
                    $svc->confirmar((int)$id, (int)$user['id']);
                    return redirect()->to('/pedidos/'.$id)->with('message','Pedido cambiado a preparando.');
                } else {
                    return redirect()->back()->with('error','Solo puedes pasar a "preparando" desde "ingresado".');
                }
            }

            if ($destino === 'procesado') {
                if ($pedido['estado'] === 'ingresado') {
                    // confirmar y luego procesar
                    $svc->confirmar((int)$id, (int)$user['id']);
                    $svc->procesar((int)$id, (int)$user['id'], $nota ?: 'Procesado desde estado ingresado');
                    return redirect()->to('/pedidos/'.$id)->with('message','Pedido confirmado y procesado.');
                }
                if ($pedido['estado'] === 'preparando') {
                    $svc->procesar((int)$id, (int)$user['id'], $nota ?: 'Pedido procesado');
                    return redirect()->to('/pedidos/'.$id)->with('message','Pedido cambiado a procesado.');
                }
                return redirect()->back()->with('error','Estado inválido para procesar.');
            }

            if ($destino === 'ingresado') {
                return redirect()->back()->with('error','No se permiten retrocesos a "ingresado".');
            }

            return redirect()->back()->with('error','Estado destino no válido.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Error: '.$e->getMessage());
        }
    }


}