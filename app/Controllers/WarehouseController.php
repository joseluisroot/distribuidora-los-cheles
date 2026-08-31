<?php
namespace App\Controllers;

use App\Services\WarehouseStructure;
use App\Services\AccessService;

final class WarehouseController extends BaseController
{
    public function index()
    {
        $structure = new WarehouseStructure();
        $installed = $structure->isInstalled();
        if (!$installed) $this->response->setStatusCode(503);
        return view('inventario/ubicaciones', [
            'title' => 'Bodegas y ubicaciones', 'installed' => $installed,
            'locations' => $installed ? $structure->locations() : [],
            'sites' => $installed ? $structure->sites() : [],
            'warehouses' => $installed ? $structure->warehouses() : [],
            'audit' => $installed ? $structure->audit() : [],
            'canAdjust' => (new AccessService())->can((int) (session('user')['id'] ?? 0), 'inventory.adjust'),
        ]);
    }

    public function createSite() { return $this->mutate(fn($s, $actor) => $s->createSite($actor, $this->request->getPost()), 'Sede registrada.'); }
    public function createWarehouse() { return $this->mutate(fn($s, $actor) => $s->createWarehouse($actor, $this->request->getPost()), 'Almacén registrado.'); }
    public function createLocation() { return $this->mutate(fn($s, $actor) => $s->createLocation($actor, $this->request->getPost()), 'Ubicación registrada.'); }

    public function changeStatus()
    {
        return $this->mutate(function ($service, $actor) {
            $service->changeStatus($actor, (string) $this->request->getPost('entity_type'),
                (int) $this->request->getPost('entity_id'), $this->request->getPost('active') === '1',
                (string) $this->request->getPost('reason'));
        }, 'Estado actualizado.');
    }

    private function mutate(callable $operation, string $message)
    {
        $actor = (new AccessService())->sessionUser();
        if (!$actor) return \App\Services\AccessDeniedResponse::make($this->request);
        try { $operation(new WarehouseStructure(), (int) $actor['id']); }
        catch (\DomainException $e) { return redirect()->to(site_url('inventario/ubicaciones'))->withInput()->with('error', $e->getMessage()); }
        return redirect()->to(site_url('inventario/ubicaciones'))->with('message', $message);
    }
}
