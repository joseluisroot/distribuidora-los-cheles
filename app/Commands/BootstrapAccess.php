<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BootstrapAccess extends BaseCommand
{
    protected $group = 'Security';
    protected $name = 'access:bootstrap';
    protected $description = 'Inicializa permisos de un usuario existente, solo una vez y desde CLI.';
    protected $usage = 'access:bootstrap <user-id>';

    public function run(array $params)
    {
        $id = filter_var($params[0] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $db = db_connect();
        if (!$id || !(new \App\Services\AccessService($db))->user($id)) {
            CLI::error('Indica el ID de un usuario activo existente.');
            return;
        }
        CLI::write('Se concederán permisos globales de administración al usuario #' . $id . '.');
        if (CLI::prompt('¿Confirmar inicialización?', ['no', 'si']) !== 'si') return;
        try {
            (new \App\Services\AccessService($db))->requireTransactionalStorage();
        } catch (\DomainException $e) {
            CLI::error($e->getMessage());
            return;
        }
        $db->transBegin();
        try {
            $db->table('access_lock')->where('id', 1)->set('version', 'version + 1', false)->update();
            if ($db->affectedRows() !== 1 || $db->table('access_grants')->countAllResults() > 0
                || $db->table('access_audit')->countAllResults() > 0) {
                throw new \RuntimeException('Ya se inicializaron accesos o falta la migración. No se permite reinicializar.');
            }
            foreach (config('Permissions')->catalog as $permission => $label) {
                // Financial data is not implied by access administration.
                if ($permission === 'costs.view') continue;
                $key = ['subject_type' => 'user', 'subject_id' => $id, 'permission' => $permission, 'scope' => 'global'];
                $db->table('access_grants')->insert($key + ['effect' => 'allow']);
                $db->table('access_audit')->insert($key + [
                    'actor_id' => null, 'action' => 'bootstrap.cli', 'before_effect' => null, 'after_effect' => 'allow',
                    'reason' => 'Inicialización explícita por operador CLI', 'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            if (!$db->transStatus()) throw new \RuntimeException('No se pudo inicializar.');
            $db->transCommit();
            CLI::write('Accesos inicializados. No se creó ni modificó una contraseña.');
        } catch (\Throwable $e) {
            $db->transRollback();
            CLI::error($e->getMessage());
        }
    }
}
