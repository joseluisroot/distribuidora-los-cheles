<?php

namespace App\Controllers;

use App\Models\ProductoModel;
use App\Services\AccessService;
use App\Services\ProductPresentationService;
use DomainException;

final class ProductPresentationController extends BaseController
{
    public function index(int $productId)
    {
        $product = (new ProductoModel())->find($productId);
        if (!$product) {
            return redirect()->to(site_url('productos'))->with('error', 'El producto solicitado no existe.');
        }

        $service = new ProductPresentationService();
        return view('productos/presentaciones', [
            'title' => 'Presentaciones de ' . $product['nombre'],
            'producto' => $product,
            'presentaciones' => $service->forProduct($productId),
        ]);
    }

    public function create(int $productId)
    {
        $actor = (new AccessService())->sessionUser();
        if (!$actor) {
            return redirect()->to(site_url('login'));
        }

        try {
            (new ProductPresentationService())->create((int) $actor['id'], $productId, $this->request->getPost());
            return redirect()->to(site_url('productos/' . $productId . '/presentaciones'))
                ->with('message', 'Presentación registrada y auditada correctamente.');
        } catch (DomainException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }
    }

    public function update(int $productId, int $presentationId)
    {
        $actor=(new AccessService())->sessionUser();
        if (!$actor) return redirect()->to(site_url('login'));
        try {
            (new ProductPresentationService())->update((int)$actor['id'],$productId,$presentationId,$this->request->getPost());
            return redirect()->to(site_url('productos/'.$productId.'/presentaciones'))->with('message','Presentación actualizada y auditada correctamente.');
        } catch (DomainException $exception) {
            return redirect()->to(site_url('productos/'.$productId.'/presentaciones'))->with('error',$exception->getMessage());
        }
    }
}
