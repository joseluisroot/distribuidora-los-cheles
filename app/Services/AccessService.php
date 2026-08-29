<?php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;

class AccessService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function user(int $id): ?array
    {
        return $this->db->table('users')->where('id', $id)
            ->where('is_active', 1)->where('deleted_at', null)->get()->getRowArray();
    }

    public function sessionUser(): ?array
    {
        $saved = session('user');
        if (!is_array($saved) || !isset($saved['id'], $saved['auth_version'])) {
            return null;
        }
        $user = $this->user((int) $saved['id']);
        return $user && (int) $user['auth_version'] === (int) $saved['auth_version'] ? $user : null;
    }

    public static function validScope(string $scope): bool
    {
        return $scope === 'global' || preg_match('/^sucursal:[1-9][0-9]{0,9}$/D', $scope) === 1;
    }

    public function explain(int $userId, string $permission, string $scope = 'global'): array
    {
        if (!isset(config('Permissions')->catalog[$permission]) || !self::validScope($scope)) {
            return ['allowed' => false, 'grants' => []];
        }
        $user = $this->user($userId);
        if (!$user) {
            return ['allowed' => false, 'grants' => []];
        }
        $query = $this->db->table('access_grants')
            ->groupStart()
                ->groupStart()->where('subject_type', 'user')->where('subject_id', $userId)->groupEnd()
                ->orGroupStart()->where('subject_type', 'role')->where('subject_id', $user['role_id'])->groupEnd()
            ->groupEnd()->where('permission', $permission);
        if ($scope === 'global') {
            // A global legacy screen cannot exclude an explicitly denied branch.
            $query->groupStart()->where('scope', 'global')->orWhere('effect', 'deny')->groupEnd();
        } else {
            $query->whereIn('scope', array_unique(['global', $scope]));
        }
        $rows = $query->get()->getResultArray();
        $effects = array_column($rows, 'effect');
        return ['allowed' => in_array('allow', $effects, true) && !in_array('deny', $effects, true), 'grants' => $rows];
    }

    public function can(int $id, string $permission, string $scope = 'global'): bool
    {
        return $this->explain($id, $permission, $scope)['allowed'];
    }

    public function change(int $actorId, string $type, int $id, string $permission, string $scope, ?string $effect, string $reason): void
    {
        $this->requireTransactionalStorage();
        if (!in_array($type, ['user', 'role'], true) || $id < 1
            || !isset(config('Permissions')->catalog[$permission])
            || !self::validScope($scope) || !in_array($effect, [null, 'allow', 'deny'], true)
            || trim($reason) === '' || mb_strlen($reason) > 500) {
            throw new DomainException('Datos de permiso inválidos.');
        }
        $this->db->transBegin();
        try {
            // Serialize permission mutations, including revocation vs delegation.
            $this->db->table('access_lock')->where('id', 1)->set('version', 'version + 1', false)->update();
            if ($this->db->affectedRows() !== 1) {
                throw new DomainException('Control de accesos no inicializado.');
            }
            $actor = $this->user($actorId);
            $target = $this->db->table($type === 'user' ? 'users' : 'roles')->where('id', $id)->get()->getRowArray();
            if (!$actor || !$target || !$this->can($actorId, 'access.manage', $scope)
                || !$this->can($actorId, $permission, $scope)
                || ($type === 'user' && $id === $actorId)
                || ($type === 'role' && $id === (int) $actor['role_id'])) {
                throw new DomainException('No puedes delegar este permiso ni modificar tus propios accesos.');
            }
            $key = ['subject_type' => $type, 'subject_id' => $id, 'permission' => $permission, 'scope' => $scope];
            $before = $this->db->table('access_grants')->where($key)->get()->getRowArray();
            if ($before) {
                $this->db->table('access_grants')->where('id', $before['id'])->delete();
            }
            if ($effect !== null) {
                $this->db->table('access_grants')->insert($key + ['effect' => $effect]);
            }
            $this->db->table('access_audit')->insert($key + [
                'actor_id' => $actorId, 'action' => 'permission.change',
                'before_effect' => $before['effect'] ?? null, 'after_effect' => $effect,
                'reason' => trim($reason), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$this->db->transStatus()) {
                throw new DomainException('No se pudo guardar el permiso.');
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function requireTransactionalStorage(): void
    {
        if ($this->db->DBDriver !== 'MySQLi') return;
        foreach (['access_lock', 'access_grants', 'access_audit'] as $table) {
            $row = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$this->db->prefixTable($table)])->getRowArray();
            if (strtoupper($row['ENGINE'] ?? '') !== 'INNODB') {
                throw new DomainException('Es necesario aplicar la migración de seguridad InnoDB antes de modificar permisos.');
            }
        }
    }
}
