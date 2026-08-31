<?php
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class SecurityRoutesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testWarehouseStructureRequiresAuthentication(): void
    {
        $this->get('inventario/ubicaciones')->assertRedirectTo(site_url('login'));
    }

    /** @dataProvider warehouseMutationPaths */
    public function testWarehouseMutationsRequireAuthentication(string $path): void
    {
        $security = service('security');
        $name = $security->getTokenName();
        $hash = $security->getHash();
        $this->withSession([$name => $hash])->post($path, [$name => $hash])->assertRedirectTo(site_url('login'));
    }

    public static function warehouseMutationPaths(): array
    {
        return array_map(static fn ($path) => [$path], ['inventario/sedes', 'inventario/almacenes',
            'inventario/ubicaciones', 'inventario/estructura/estado']);
    }

    public function testOrderScreensRenderOnlyOneMainNavigation(): void
    {
        $data = ['pedidos' => [], 'detalles' => [], 'historial' => [],
            'pedido' => ['id' => 1, 'cliente' => 'Cliente de prueba', 'estado' => 'ingresado',
                'total' => 0, 'created_at' => '2026-08-28 12:00:00']];
        foreach (['pedidos/index', 'pedidos/ver'] as $view) {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . view($view, $data));
            $xpath = new \DOMXPath($dom);
            $this->assertSame(1, (int) $xpath->evaluate('count(//nav[.//a[normalize-space(.)="Distribuidora Los Cheles"]])'), $view);
        }
    }

    public function testAdminInventoryLinksUseExistingProductRoute(): void
    {
        $html = view('admin/dashboard', [
            'kpi' => [], 'ultimosPedidos' => [['id' => 7, 'created_at' => '2026-08-28 12:00:00']], 'stockCritico' => null, 'ventas7d' => [],
        ]);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//a[normalize-space(.)="Ver Inventario" or normalize-space(.)="Ver todos los productos"]');
        $this->assertCount(2, $links);
        foreach ($links as $link) {
            $this->assertSame(site_url('productos'), $link->getAttribute('href'));
        }
        $this->assertSame(site_url('pedidos'), $xpath->evaluate('string(//a[normalize-space(.)="Ver pedidos"]/@href)'));
        $this->assertSame(site_url('pedidos/7'), $xpath->evaluate('string(//a[normalize-space(.)="Ver pedido"]/@href)'));
        $this->assertStringNotContainsString('orders/create', $html);
        $this->assertStringNotContainsString('Total facturado', $html);
        $this->assertStringContainsString('Indicador pendiente de implementar', $html);
        $this->assertStringContainsString('Consulta de stock crítico no disponible.', $html);
    }

    public function testRegistrationRendersAccessibleFormWithLocalAssets(): void
    {
        $result = $this->get('register');
        $result->assertOK();
        $result->assertSee('Crea tu cuenta');
        $result->assertSeeElement('input#register-email');
        $result->assertSeeElement('input#register-password');
        $html = $result->response()->getBody();
        $this->assertStringContainsString(base_url('css/app.css') . '?v=', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringContainsString('rel="icon" type="image/svg+xml" href="' . base_url('assets/logo-los-cheles.svg'), $html);
        $this->assertStringContainsString('for="register-email"', $html);
        $this->assertStringContainsString('name="' . csrf_token() . '"', $html);
        $this->assertStringContainsString(base_url('assets/css/register.css'), $html);
        $this->assertStringContainsString(base_url('assets/js/register.js'), $html);
    }

    public function testDashboardHasOneLogoutAndModernModuleNavigation(): void
    {
        session()->set('user', ['id' => 1, 'name' => 'Jose Luis Reyes Ortiz', 'role' => 'admin']);
        $html = view('dashboard/index', ['user' => ['name' => 'Jose Luis Reyes Ortiz'],
            'canManageProducts' => true, 'canManageAccess' => true, 'canViewInventory' => true]);
        session()->remove('user');
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($dom);

        $this->assertSame(0, substr_count(file_get_contents(APPPATH . 'Views/dashboard/index.php'), "site_url('logout')"));
        $this->assertSame(1, substr_count(file_get_contents(APPPATH . 'Views/partials/navbar.php'), "site_url('logout')"));
        $this->assertSame(6, (int) $xpath->evaluate('count(//a[contains(concat(" ",normalize-space(@class)," ")," workspace-module ")])'));
        $this->assertStringContainsString(base_url('assets/css/dashboard.css'), $html);
        $this->assertStringContainsString('Solo aparecen los módulos autorizados', $html);
    }

    public function testImageManagementRequiresAuthentication(): void
    {
        $this->get('productos/1/imagenes')->assertRedirectTo(site_url('login'));
    }

    public function testPermissionAdministrationRequiresAuthentication(): void
    {
        $this->get('admin/accesos')->assertRedirectTo(site_url('login'));
    }

    /** @dataProvider removedPaths */
    public function testLegacyMutatingGetRoutesAreAbsent(string $path): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get($path);
    }

    public static function removedPaths(): array
    {
        return array_map(static fn ($path) => [$path],
            ['logout', 'demo/confirmar', 'migracion', 'seed/run', 'productos/eliminar/1', 'carretilla/clear']);
    }

    public function testLoginRejectsPostWithoutCsrfToken(): void
    {
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->post('login', ['email' => 'someone@example.com', 'password' => 'not-a-real-password'])->assertStatus(403);
    }

    public function testValidCsrfPostReachesInputValidation(): void
    {
        $security = service('security');
        $token = $security->getHash();
        $name = $security->getTokenName();
        $result = $this->withSession([$name => $token])->post('login', [$name => $token]);
        $result->assertRedirect();
        $result->assertSessionHas('error');
    }
}
