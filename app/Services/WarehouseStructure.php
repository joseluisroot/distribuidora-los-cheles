<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

/** Read-only boundary for the first I2 delivery. Mutations will require their own audited workflow. */
final class WarehouseStructure
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function isInstalled(): bool
    {
        return $this->db->tableExists('sedes') && $this->db->tableExists('almacenes')
            && $this->db->tableExists('ubicaciones');
    }

    public function locations(): array
    {
        return $this->db->table('ubicaciones u')
            ->select('u.id,u.codigo,u.pasillo,u.fila,u.columna,u.activa, a.nombre AS almacen,a.tipo AS tipo_almacen,a.activa AS almacen_activo,s.nombre AS sede,s.tipo AS tipo_sede,s.activa AS sede_activa')
            ->join('almacenes a', 'a.id=u.almacen_id')->join('sedes s', 's.id=a.sede_id')
            ->orderBy('s.codigo')->orderBy('a.codigo')->orderBy('u.codigo')->get()->getResultArray();
    }

    public function sites(): array
    {
        return $this->db->table('sedes')->orderBy('codigo')->get()->getResultArray();
    }

    public function warehouses(): array
    {
        return $this->db->table('almacenes a')->select('a.*,s.nombre AS sede,s.activa AS sede_activa')
            ->join('sedes s', 's.id=a.sede_id')->orderBy('s.codigo')->orderBy('a.codigo')->get()->getResultArray();
    }

    public function audit(int $limit = 30): array
    {
        if (!$this->db->tableExists('warehouse_audit')) return [];
        return $this->db->table('warehouse_audit')->orderBy('id', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function createSite(int $actorId, array $input): int
    {
        $data = ['codigo' => $this->code($input['codigo'] ?? '', 30),
            'nombre' => $this->name($input['nombre'] ?? ''), 'tipo' => (string) ($input['tipo'] ?? ''),
            'activa' => 1, 'created_at' => date('Y-m-d H:i:s')];
        if (!in_array($data['tipo'], ['central', 'sucursal'], true)) throw new DomainException('Selecciona un tipo de sede válido.');
        if ($this->db->table('sedes')->where('codigo', $data['codigo'])->countAllResults()) throw new DomainException('El código de sede ya existe.');
        return $this->insertAudited($actorId, 'sede', 'sedes', $data, $input['reason'] ?? '');
    }

    public function createWarehouse(int $actorId, array $input): int
    {
        $siteId = $this->positiveId($input['sede_id'] ?? 0);
        if (!$this->db->table('sedes')->where('id', $siteId)->countAllResults()) throw new DomainException('La sede seleccionada no existe.');
        $data = ['sede_id' => $siteId, 'codigo' => $this->code($input['codigo'] ?? '', 30),
            'nombre' => $this->name($input['nombre'] ?? ''), 'tipo' => (string) ($input['tipo'] ?? ''),
            'activa' => 1, 'created_at' => date('Y-m-d H:i:s')];
        if (!in_array($data['tipo'], ['bodega', 'sala'], true)) throw new DomainException('Selecciona un tipo de almacén válido.');
        if ($this->db->table('almacenes')->where(['sede_id' => $siteId, 'codigo' => $data['codigo']])->countAllResults()) throw new DomainException('El código de almacén ya existe en esa sede.');
        return $this->insertAudited($actorId, 'almacen', 'almacenes', $data, $input['reason'] ?? '');
    }

    public function createLocation(int $actorId, array $input): int
    {
        $warehouseId = $this->positiveId($input['almacen_id'] ?? 0);
        if (!$this->db->table('almacenes')->where('id', $warehouseId)->countAllResults()) throw new DomainException('El almacén seleccionado no existe.');
        $data = ['almacen_id' => $warehouseId, 'codigo' => $this->code($input['codigo'] ?? '', 50),
            'pasillo' => $this->position($input['pasillo'] ?? '', 'pasillo'),
            'fila' => $this->position($input['fila'] ?? '', 'fila'),
            'columna' => $this->position($input['columna'] ?? '', 'columna'),
            'activa' => 1, 'created_at' => date('Y-m-d H:i:s')];
        $locations = $this->db->table('ubicaciones')->where('almacen_id', $warehouseId);
        if ((clone $locations)->where('codigo', $data['codigo'])->countAllResults()) throw new DomainException('El código de ubicación ya existe en ese almacén.');
        if ((clone $locations)->where(['pasillo' => $data['pasillo'], 'fila' => $data['fila'], 'columna' => $data['columna']])->countAllResults()) throw new DomainException('La posición física ya existe en ese almacén.');
        return $this->insertAudited($actorId, 'ubicacion', 'ubicaciones', $data, $input['reason'] ?? '');
    }

    public function changeStatus(int $actorId, string $type, int $id, bool $active, string $reason): void
    {
        $tables = ['sede' => 'sedes', 'almacen' => 'almacenes', 'ubicacion' => 'ubicaciones'];
        if (!isset($tables[$type]) || $id < 1) throw new DomainException('Elemento de estructura inválido.');
        $reason = $this->reason($reason);
        $before = $this->db->table($tables[$type])->where('id', $id)->get()->getRowArray();
        if (!$before) throw new DomainException('El elemento seleccionado no existe.');
        $after = $before; $after['activa'] = $active ? 1 : 0;
        $this->transaction(function () use ($actorId, $type, $id, $tables, $before, $after, $reason) {
            $this->db->table($tables[$type])->where('id', $id)->update(['activa' => $after['activa']]);
            $this->writeAudit($actorId, $type, $id, 'status.change', $before, $after, $reason);
        });
    }

    private function insertAudited(int $actorId, string $type, string $table, array $data, string $reason): int
    {
        $reason = $this->reason($reason); $id = 0;
        try {
            $this->transaction(function () use ($actorId, $type, $table, $data, $reason, &$id) {
                $this->db->table($table)->insert($data); $id = (int) $this->db->insertID();
                $this->writeAudit($actorId, $type, $id, 'create', null, ['id' => $id] + $data, $reason);
            });
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            throw new DomainException('El código o la posición ya existe dentro de su nivel.', 0, $e);
        }
        return $id;
    }

    private function writeAudit(int $actorId, string $type, int $id, string $action, ?array $before, array $after, string $reason): void
    {
        $this->db->table('warehouse_audit')->insert(['actor_id' => $actorId, 'entity_type' => $type,
            'entity_id' => $id, 'action' => $action, 'before_data' => $before ? json_encode($before) : null,
            'after_data' => json_encode($after), 'reason' => $reason, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function transaction(callable $operation): void
    {
        if (!$this->db->tableExists('warehouse_audit')) throw new DomainException('Aplica la migración de auditoría antes de modificar la estructura.');
        $this->db->transBegin();
        try { $operation(); if (!$this->db->transStatus()) throw new DomainException('No se pudo guardar el cambio.'); $this->db->transCommit(); }
        catch (\Throwable $e) { $this->db->transRollback(); throw $e; }
    }

    private function positiveId($value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) throw new DomainException('Selecciona un registro válido.');
        return (int) $id;
    }

    private function code($value, int $max): string
    {
        $value = strtoupper(trim((string) $value));
        if (!preg_match('/^[A-Z0-9][A-Z0-9-]{1,'.($max - 1).'}$/D', $value)) throw new DomainException('El código debe usar entre 2 y '.$max.' caracteres: letras, números o guiones.');
        return $value;
    }

    private function name($value): string
    {
        $value = trim((string) $value);
        if (mb_strlen($value) < 3 || mb_strlen($value) > 120) throw new DomainException('El nombre debe tener entre 3 y 120 caracteres.');
        return $value;
    }

    private function position($value, string $label): string
    {
        $value = strtoupper(trim((string) $value));
        if ($value === '' || mb_strlen($value) > 30) throw new DomainException('Indica una '.$label.' válida.');
        return $value;
    }

    private function reason($value): string
    {
        $value = trim((string) $value);
        if ($value === '' || mb_strlen($value) > 500) throw new DomainException('Indica el motivo del cambio (máximo 500 caracteres).');
        return $value;
    }

    /** Structural eligibility only: not availability, payment approval or a stock reservation. */
    public function canSupplyWeb(int $locationId): bool
    {
        if ($locationId < 1) return false;
        return $this->db->table('ubicaciones u')->join('almacenes a', 'a.id=u.almacen_id')
            ->join('sedes s', 's.id=a.sede_id')->where('u.id', $locationId)
            ->where('u.activa', 1)->where('a.activa', 1)->where('s.activa', 1)
            ->where('a.tipo', 'bodega')->where('s.tipo', 'sucursal')->countAllResults() === 1;
    }
}
