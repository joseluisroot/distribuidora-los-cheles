<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\PedidoModel;
use App\Models\ProductoModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $pedidoModel   = new PedidoModel();
        $productoModel = new ProductoModel();
        $clienteModel  = new ClienteModel();

        $today = date('Y-m-d');

        $kpi = [
            'ventasHoy'         => $pedidoModel->getTotalByDate($today),
            'pedidosPendientes' => null, // Pending implementation; do not present a placeholder zero as real data.
            'stockBajo'         => null,
            'clientesActivos'   => null,
        ];

        // Cálculos que podrían fallar si no existen columnas/tablas:
        try { $kpi['stockBajo'] = $productoModel->countLowStock(10); } catch (\Throwable $e) {}
        try { $kpi['clientesActivos'] = $clienteModel->countActive(); } catch (\Throwable $e) {}

        $data = [
            'kpi'            => $kpi,
            'ultimosPedidos' => $pedidoModel->getRecentOrders(10),
            'stockCritico'   => null,
            'ventas7d'       => $pedidoModel->getSalesLastDays(7),
        ];

        try { $data['stockCritico'] = $productoModel->getLowStock(10, 10); } catch (\Throwable $e) {}

        return view('admin/dashboard', $data);
    }
}
