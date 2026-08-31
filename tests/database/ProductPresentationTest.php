<?php

use App\Services\ProductPresentationService;
use CodeIgniter\Test\CIUnitTestCase;

final class ProductPresentationTest extends CIUnitTestCase
{
    private $presentationDb; private $presentationForge;
    protected function setUp(): void
    {
        parent::setUp(); $this->presentationDb=db_connect('tests'); $this->presentationForge=\Config\Database::forge($this->presentationDb);
        $this->presentationForge->addField(['id'=>['type'=>'INT','unsigned'=>true,'auto_increment'=>true],'sku'=>['type'=>'VARCHAR','constraint'=>50]]);
        $this->presentationForge->addKey('id',true); $this->presentationForge->createTable('productos');
        $this->presentationDb->table('productos')->insert(['id'=>1,'sku'=>'SKU-TEST']);
        require_once APPPATH.'Database/Migrations/2026-08-30-000001_CreateProductPresentations.php';
        (new \App\Database\Migrations\CreateProductPresentations($this->presentationForge))->up();
    }
    protected function tearDown(): void
    {
        foreach(['producto_presentacion_audit','producto_presentaciones','productos'] as $table) $this->presentationForge->dropTable($table,true);
        parent::tearDown();
    }

    public function testUnitAndBundleUseIndependentWholesaleThresholds(): void
    {
        $service=new ProductPresentationService($this->presentationDb);
        $service->create(7,1,['codigo'=>'unidad','nombre'=>'Unidad','unidades_base'=>1,'precio_detalle'=>'3.00','precio_mayoreo'=>'2.50','reason'=>'Definición comercial']);
        $service->create(7,1,['codigo'=>'fardo-10','nombre'=>'Fardo de 10','unidades_base'=>10,'precio_detalle'=>'25','precio_mayoreo'=>'22','reason'=>'Definición comercial']);
        $rows=$service->forProduct(1); $unit=$rows[0]; $bundle=$rows[1];
        $this->assertSame('3.00',$service->priceForQuantity($unit,2));
        $this->assertSame('2.50',$service->priceForQuantity($unit,3));
        $this->assertSame('25.00',$service->priceForQuantity($bundle,1));
        $this->assertSame('22.00',$service->priceForQuantity($bundle,3));
        $this->assertSame(10,(int)$bundle['unidades_base']);
        $this->assertSame(2,$this->presentationDb->table('producto_presentacion_audit')->countAllResults());
    }

    public function testRejectsInvalidPricingAndDuplicateCodesWithoutAudit(): void
    {
        $service=new ProductPresentationService($this->presentationDb);
        foreach ([
            ['codigo'=>'UNIDAD','nombre'=>'Unidad','unidades_base'=>1,'precio_detalle'=>'2','precio_mayoreo'=>'3','reason'=>'Inválido'],
            ['codigo'=>'X','nombre'=>'X','unidades_base'=>0,'precio_detalle'=>'2','precio_mayoreo'=>'1','reason'=>'Inválido'],
        ] as $input) {
            try { $service->create(7,1,$input); $this->fail('Expected validation error.'); }
            catch (\DomainException $e) { $this->assertNotSame('',$e->getMessage()); }
        }
        $this->assertSame(0,$this->presentationDb->table('producto_presentaciones')->countAllResults());
        $this->assertSame(0,$this->presentationDb->table('producto_presentacion_audit')->countAllResults());
    }

    public function testPresentationCanBeUpdatedWithAudit(): void
    {
        $service=new ProductPresentationService($this->presentationDb);
        $id=$service->create(7,1,['codigo'=>'UNIDAD','nombre'=>'Unidad','unidades_base'=>1,'precio_detalle'=>'3.00','precio_mayoreo'=>'2.50','reason'=>'Alta']);
        $service->update(8,1,$id,['codigo'=>'UNIDAD','nombre'=>'Unidad individual','unidades_base'=>1,'precio_detalle'=>'3.25','precio_mayoreo'=>'2.75','activa'=>'1','reason'=>'Ajuste autorizado']);
        $row=$this->presentationDb->table('producto_presentaciones')->where('id',$id)->get()->getRowArray();
        $this->assertSame('3.25',number_format((float)$row['precio_detalle'],2,'.',''));
        $this->assertSame('Unidad individual',$row['nombre']);
        $events=$this->presentationDb->table('producto_presentacion_audit')->where('presentacion_id',$id)->orderBy('id')->get()->getResultArray();
        $this->assertCount(2,$events);
        $this->assertSame('update',$events[1]['action']);
        $this->assertSame(8,(int)$events[1]['actor_id']);
        $this->assertSame('Ajuste autorizado',$events[1]['reason']);
        $this->assertSame(3.0,(float)json_decode($events[1]['before_data'],true)['precio_detalle']);
        $this->assertSame(3.25,(float)json_decode($events[1]['after_data'],true)['precio_detalle']);
    }
}
