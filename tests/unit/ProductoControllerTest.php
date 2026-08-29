<?php

use App\Controllers\ProductoController;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

final class ProductoControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    public function testCrearValidatesUppercasePostInsteadOfRenderingForm(): void
    {
        $request = service('incomingrequest');
        $request->setMethod('POST');
        $request->setGlobal('post', []);

        $result = $this->withRequest($request)
            ->controller(ProductoController::class)
            ->execute('crear');

        $result->assertRedirect();
        $result->assertSessionHas('errors');
        $this->assertArrayHasKey('sku', session('errors'));
    }
}
