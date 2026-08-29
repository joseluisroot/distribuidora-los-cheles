<?php
namespace App\Controllers;

use App\Services\AccessService;

class AccessController extends BaseController
{
    public function index()
    {
        $service = new AccessService();
        $requestedId = $this->request->getGet('user_id');
        $id = $requestedId === null
            ? (int) ($service->sessionUser()['id'] ?? 0)
            : filter_var($requestedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id || !$service->user($id)) {
            return $this->response->setStatusCode(400)->setBody('Selecciona un usuario activo existente.');
        }
        $scope = (string) ($this->request->getGet('scope') ?? 'global');
        if (!AccessService::validScope($scope)) {
            return $this->response->setStatusCode(400)->setBody('Ámbito inválido.');
        }
        $effective = [];
        foreach (config('Permissions')->catalog as $permission => $label) {
            $effective[$permission] = $service->explain($id, $permission, $scope);
        }
        return view('access/index', [
            'title' => 'Permisos y auditoría', 'catalog' => config('Permissions')->catalog,
            'users' => db_connect()->table('users')->select('id,name')->where('deleted_at', null)->get()->getResultArray(),
            'roles' => db_connect()->table('roles')->select('id,name')->get()->getResultArray(),
            'effective' => $effective, 'selectedId' => $id, 'scope' => $scope,
            'audit' => db_connect()->table('access_audit')->orderBy('id', 'DESC')->limit(50)->get()->getResultArray(),
        ]);
    }

    public function update()
    {
        $service = new AccessService();
        $actor = $service->sessionUser();
        if (!$actor) {
            return \App\Services\AccessDeniedResponse::make($this->request);
        }
        try {
            $effect = (string) $this->request->getPost('effect');
            $service->change((int) $actor['id'], (string) $this->request->getPost('subject_type'),
                (int) $this->request->getPost('subject_id'), (string) $this->request->getPost('permission'),
                (string) $this->request->getPost('scope'), $effect === 'remove' ? null : $effect,
                (string) $this->request->getPost('reason'));
        } catch (\DomainException $e) {
            return redirect()->to(site_url('admin/accesos'))->with('error', $e->getMessage());
        }
        return redirect()->to(site_url('admin/accesos'))->with('message', 'Permiso actualizado y auditado.');
    }
}
