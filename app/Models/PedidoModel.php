<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['cliente_id','estado','total','observaciones'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function totalizarPedido(int $pedidoId): float
    {
        $rows = (new PedidoDetalleModel())->select('subtotal')->where('pedido_id',$pedidoId)->findAll();
        $total = 0.0;
        foreach ($rows as $r) $total += (float)$r['subtotal'];
        return round($total, 2, PHP_ROUND_HALF_UP);
    }

    /** Ventas del día basadas en pedido_detalle.created_at */
    public function getTotalByDate(string $ymd): float
    {
        $row = $this->select('COALESCE(SUM(total),0) AS total')
            ->where('DATE(created_at)', $ymd)
            ->get()->getRowArray();

        return (float) ($row['total'] ?? 0);
    }

    /** Ventas últimos N días: [{fecha, total}] */
    public function getSalesLastDays(int $days = 7): array
    {
        $sql = "
            SELECT DATE(created_at) AS fecha,
                   COALESCE(SUM(total),0) AS total
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ";
        return $this->db->query($sql)->getResultArray();
    }

    /**
     * Últimos pedidos (resumen por pedido_id desde detalle).
     * Si luego tienes tabla 'pedidos' con cliente/status, haz JOIN para enriquecer.
     */
    public function getRecentOrders(int $limit = 10): array
    {
        $sql = "
            SELECT pd.id, pd.total, pd.created_at, pd.estado AS status
            FROM {$this->table} pd
            ORDER BY created_at DESC
            LIMIT {$limit}
        ";
        $rows = $this->db->query($sql)->getResultArray();

        // Campos “customer_name” y “status” placeholders por ahora.
        foreach ($rows as &$r) {
            $r['customer_name'] = '—';
        }
        return $rows;
    }

    /**
     * Conteo de pedidos pendientes:
     * Requiere tabla de cabecera 'pedidos' con campo 'status'.
     * Si aún no existe, devolvemos 0 para no romper el dashboard.
     */
    public function countPendingFromHeader(): int
    {
        // Descomenta cuando exista la tabla de cabecera:
        // $sql = "SELECT COUNT(*) AS c FROM pedidos WHERE status = 'PENDIENTE'";
        // $row = $this->db->query($sql)->getRowArray();
        // return (int) ($row['c'] ?? 0);
        return 0;
    }
}


















































