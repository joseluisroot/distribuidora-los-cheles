<?php

use CodeIgniter\Test\CIUnitTestCase;
final class ErrorPagesTest extends CIUnitTestCase
{
    public function testNotFoundViewIsBrandedAndAccessible(): void
    {
        $html = view('errors/html/error_404', ['message' => "Can't find a route"]);

        $this->assertStringContainsString('No encontramos lo que buscabas', $html);
        $this->assertStringContainsString('Ir al catálogo', $html);
        $this->assertStringContainsString('aria-labelledby="error-title"', $html);
        $this->assertStringContainsString(base_url('assets/css/error-pages.css'), $html);
        $this->assertStringContainsString('noindex, nofollow', $html);
        $this->assertStringContainsString('Detalle para desarrollo', $html);
    }

    public function testProductionErrorViewDoesNotRequireExceptionDetails(): void
    {
        $html = view('errors/html/production');

        $this->assertStringContainsString('Algo no salió como esperábamos', $html);
        $this->assertStringContainsString('No pudimos completar la solicitud', $html);
        $this->assertStringContainsString('assets/css/error-pages.css', $html);
        $this->assertStringNotContainsString('exception', strtolower($html));
        $this->assertStringNotContainsString('backtrace', strtolower($html));
    }
}
