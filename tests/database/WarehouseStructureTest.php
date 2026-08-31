<?php
use App\Services\WarehouseStructure;
use CodeIgniter\Test\CIUnitTestCase;

final class WarehouseStructureTest extends CIUnitTestCase
{
    private $warehouseDb;
    private $warehouseForge;
    private $migration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->warehouseDb = db_connect('tests');
        $this->assertSame('SQLite3', $this->warehouseDb->DBDriver);
        $this->assertSame(':memory:', $this->warehouseDb->database);
        $this->warehouseForge = \Config\Database::forge($this->warehouseDb);
        require_once APPPATH . 'Database/Migrations/2026-08-28-000002_CreateWarehouseStructure.php';
        $this->migration = new \App\Database\Migrations\CreateWarehouseStructure($this->warehouseForge);
        $this->migration->up();
        require_once APPPATH . 'Database/Migrations/2026-08-29-000002_CreateWarehouseAudit.php';
        (new \App\Database\Migrations\CreateWarehouseAudit($this->warehouseForge))->up();
    }

    protected function tearDown(): void
    {
        foreach (['warehouse_audit', 'ubicaciones', 'almacenes', 'sedes'] as $table) $this->warehouseForge->dropTable($table, true);
        parent::tearDown();
    }

    private function seedStructure(): void
    {
        $date = '2026-08-28 12:00:00';
        foreach ([1 => 'central', 2 => 'sucursal'] as $id => $type) {
            $this->warehouseDb->table('sedes')->insert(['id' => $id, 'codigo' => 'S'.$id, 'nombre' => 'Sede de prueba '.$id, 'tipo' => $type, 'created_at' => $date]);
        }
        foreach ([1 => [1, 'bodega'], 2 => [2, 'bodega'], 3 => [2, 'sala']] as $id => [$site, $type]) {
            $this->warehouseDb->table('almacenes')->insert(['id' => $id, 'sede_id' => $site, 'codigo' => 'A'.$id, 'nombre' => 'Almacén de prueba', 'tipo' => $type, 'created_at' => $date]);
            $this->warehouseDb->table('ubicaciones')->insert(['id' => $id, 'almacen_id' => $id, 'codigo' => 'A-1-2', 'pasillo' => 'A', 'fila' => '1', 'columna' => '2', 'created_at' => $date]);
        }
    }

    public function testMigrationStartsEmptyAndCanBeRolledBack(): void
    {
        $service = new WarehouseStructure($this->warehouseDb);
        $this->assertTrue($service->isInstalled());
        $this->assertSame([], $service->locations());
        $this->migration->down();
        $this->assertFalse($service->isInstalled());
    }

    public function testWebEligibilityExcludesCentralSalesFloorAndInactiveParents(): void
    {
        $this->seedStructure();
        $service = new WarehouseStructure($this->warehouseDb);
        $this->assertFalse($service->canSupplyWeb(1));
        $this->assertTrue($service->canSupplyWeb(2));
        $this->assertFalse($service->canSupplyWeb(3));
        $this->assertFalse($service->canSupplyWeb(999));
        foreach (['ubicaciones', 'almacenes', 'sedes'] as $table) {
            $this->warehouseDb->table($table)->where('id', 2)->update(['activa' => 0]);
            $this->assertFalse($service->canSupplyWeb(2), $table);
            $this->warehouseDb->table($table)->where('id', 2)->update(['activa' => 1]);
        }
        $this->assertCount(3, $service->locations());
    }

    public function testPositionCannotBeDuplicatedWithinWarehouse(): void
    {
        $this->seedStructure();
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->warehouseDb->table('ubicaciones')->insert(['almacen_id' => 2, 'codigo' => 'OTRO',
            'pasillo' => 'A', 'fila' => '1', 'columna' => '2', 'created_at' => '2026-08-28 12:00:00']);
    }

    public function testLocationScreenDistinguishesMissingMigrationFromEmptyData(): void
    {
        $html = view('inventario/ubicaciones', ['installed' => false, 'locations' => []]);
        $this->assertStringContainsString('pendiente de instalación', $html);
        $html = view('inventario/ubicaciones', ['installed' => true, 'locations' => []]);
        $this->assertStringContainsString('Todavía no hay ubicaciones', $html);
    }

    public function testCreatesHierarchyAndAuditsEveryRegistration(): void
    {
        $service = new WarehouseStructure($this->warehouseDb);
        $site = $service->createSite(7, ['codigo' => 'suc-01', 'nombre' => 'Sucursal principal', 'tipo' => 'sucursal', 'reason' => 'Apertura autorizada']);
        $warehouse = $service->createWarehouse(7, ['sede_id' => $site, 'codigo' => 'bod-01', 'nombre' => 'Bodega web', 'tipo' => 'bodega', 'reason' => 'Separar inventario web']);
        $location = $service->createLocation(7, ['almacen_id' => $warehouse, 'codigo' => 'a-01-02', 'pasillo' => 'a', 'fila' => '1', 'columna' => '2', 'reason' => 'Posición física validada']);

        $this->assertTrue($service->canSupplyWeb($location));
        $this->assertCount(3, $service->audit());
        $this->assertSame('SUC-01', $service->sites()[0]['codigo']);
    }

    public function testInvalidOrDuplicateRegistrationDoesNotCreateAudit(): void
    {
        $service = new WarehouseStructure($this->warehouseDb);
        $service->createSite(7, ['codigo' => 'SUC-01', 'nombre' => 'Sucursal principal', 'tipo' => 'sucursal', 'reason' => 'Registro inicial']);
        try {
            $service->createSite(7, ['codigo' => 'SUC-01', 'nombre' => 'Otra sucursal', 'tipo' => 'sucursal', 'reason' => 'Duplicado']);
            $this->fail('Expected duplicate rejection.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('ya existe', $e->getMessage());
        }
        $this->assertCount(1, $service->audit());
    }

    public function testManagementFormsAreHiddenFromReadOnlyView(): void
    {
        $data = ['installed' => true, 'sites' => [], 'warehouses' => [], 'locations' => [], 'audit' => []];
        $readOnly = view('inventario/ubicaciones', $data + ['canAdjust' => false]);
        $manager = view('inventario/ubicaciones', $data + ['canAdjust' => true]);

        $this->assertStringNotContainsString('Registrar estructura física', $readOnly);
        $this->assertStringContainsString('Registrar estructura física', $manager);
        $this->assertStringContainsString(csrf_token(), $manager);
        $this->assertStringNotContainsString('costo', strtolower($manager));
    }
}
