<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Permissions extends BaseConfig
{
    public array $catalog = [
        'access.manage' => 'Administrar accesos',
        'dashboard.view' => 'Panel administrativo',
        'products.manage' => 'Administrar productos e imágenes',
        'prices.manage' => 'Modificar precios',
        'inventory.view' => 'Consultar inventario',
        'inventory.adjust' => 'Ajustar inventario',
        'inventory.import' => 'Importar inventario',
        'orders.view' => 'Consultar pedidos comerciales',
        'orders.change' => 'Cambiar pedidos',
        'payments.verify' => 'Verificar pagos',
        'reservations.release' => 'Liberar reservas',
        'transfers.confirm' => 'Confirmar traslados',
        'refunds.manage' => 'Resolver reembolsos',
        'shipping.manage' => 'Modificar tarifas',
        'costs.view' => 'Consultar costos',
    ];
}
