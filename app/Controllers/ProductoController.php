<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;
use App\Models\MovimientoInventarioModel;
use App\Models\PrecioEscaladoModel;
use App\Models\ProductoModel;
use App\Services\InventarioService;

class ProductoController extends BaseController
{
    protected $productoModel;
    protected $inventarioModel;
    protected $escalaModel;

    public function __construct()
    {
        $this->productoModel  = new ProductoModel();
        $this->inventarioModel= new InventarioModel();
        $this->escalaModel    = new PrecioEscaladoModel();
    }

    // Listar productos
    public function index()
    {
        $productos = $this->productoModel
            ->select('productos.*, inventarios.stock')
            ->join('inventarios','inventarios.producto_id=productos.id','left')
            ->orderBy('productos.created_at','DESC')
            ->findAll();

        return view('productos/index', [
            'productos'=>$productos,
            'title'=>'Productos'
        ]);
    }

    // Crear producto (form + guardar)
    public function crear()
    {
        if ($this->request->getMethod()==='post') {
            $data = [
                'sku'=>$this->request->getPost('sku'),
                'nombre'=>$this->request->getPost('nombre'),
                'descripcion'=>$this->request->getPost('descripcion'),
                'precio_base'=>$this->request->getPost('precio_base'),
                'is_activo'=>$this->request->getPost('is_activo') ? 1 : 0,
            ];

            $id = $this->productoModel->insert($data,true);

            // inventario inicial
            $this->inventarioModel->insert([
                'producto_id'=>$id,
                'stock'=>$this->request->getPost('stock') ?? 0,
            ]);

            return redirect()->to('/productos')->with('message','Producto creado');
        }

        return view('productos/crear',['title'=>'Nuevo producto']);
    }

    // Editar producto
    public function editar($id)
    {
        $producto = $this->productoModel->find($id);
        $inventario = $this->inventarioModel->where('producto_id',$id)->first();

        if ($this->request->getMethod()==='post') {
            $data = [
                'sku'=>$this->request->getPost('sku'),
                'nombre'=>$this->request->getPost('nombre'),
                'descripcion'=>$this->request->getPost('descripcion'),
                'precio_base'=>$this->request->getPost('precio_base'),
                'is_activo'=>$this->request->getPost('is_activo') ? 1 : 0,
            ];
            $this->productoModel->update($id,$data);

            $this->inventarioModel->where('producto_id',$id)
                ->set(['stock'=>$this->request->getPost('stock') ?? 0])
                ->update();

            return redirect()->to('/productos')->with('message','Producto actualizado');
        }

        return view('productos/editar',[
            'producto'=>$producto,
            'inventario'=>$inventario,
            'title'=>'Editar producto'
        ]);
    }

    // Escalas de precios
    public function escalas($id)
    {
        $producto = $this->productoModel->find($id);
        $escalas = $this->escalaModel->where('producto_id',$id)->orderBy('min_cantidad')->findAll();

        if ($this->request->getMethod()==='post') {
            $this->escalaModel->insert([
                'producto_id'=>$id,
                'min_cantidad'=>$this->request->getPost('min_cantidad'),
                'precio'=>$this->request->getPost('precio'),
            ]);
            return redirect()->to('/productos/escalas/'.$id)->with('message','Escala agregada');
        }

        return view('productos/escalas',[
            'producto'=>$producto,
            'escalas'=>$escalas,
            'title'=>'Escalas de precios'
        ]);
    }

    // Eliminar producto (soft delete)
    public function eliminar($id)
    {
        $this->productoModel->delete($id);
        return redirect()->to('/productos')->with('message','Producto eliminado');
    }

    // Ver Kardex + filtro fechas
    public function kardex($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->to('/productos')->with('error','Producto no encontrado');

        $stockActual = (int)($this->inventarioModel->where('producto_id',$id)->first()['stock'] ?? 0);

        // Filtros (opcionales): ?from=YYYY-MM-DD&to=YYYY-MM-DD
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $movModel = new MovimientoInventarioModel();
        $builder = $movModel->where('producto_id',$id)->orderBy('created_at','ASC');

        if ($from) $builder->where('DATE(created_at) >=', $from);
        if ($to)   $builder->where('DATE(created_at) <=', $to);

        $movs = $builder->findAll();

        // Calcular saldo acumulado
        $saldo = 0;
        foreach ($movs as &$m) {
            $delta = ($m['tipo']==='entrada') ? (int)$m['cantidad'] : -(int)$m['cantidad'];
            $saldo += $delta;
            $m['saldo'] = $saldo;
        }
        unset($m);

        return view('productos/kardex', [
            'producto'    => $producto,
            'stockActual' => $stockActual,
            'movimientos' => $movs,
            'from'        => $from,
            'to'          => $to,
            'title'       => 'Kardex - '.$producto['nombre'],
        ]);
    }

// Alta manual de movimiento (entrada/salida)
    public function movimiento($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->back()->with('error','Producto no encontrado');

        $tipo       = $this->request->getPost('tipo');        // entrada|salida
        $cantidad   = (int)$this->request->getPost('cantidad');
        $referencia = trim((string)$this->request->getPost('referencia'));
        $detalle    = trim((string)$this->request->getPost('detalle'));

        if (!in_array($tipo, ['entrada','salida']) || $cantidad < 1) {
            return redirect()->back()->with('error','Datos de movimiento inválidos.');
        }

        try {
            $svc = new InventarioService();
            $svc->ajustar((int)$id, $cantidad, $tipo, $referencia ?: 'AJUSTE', $detalle ?: 'Ajuste manual');
            return redirect()->to('/productos/kardex/'.$id)->with('message','Movimiento registrado.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error','Error: '.$e->getMessage());
        }
    }

    private function getKardexQuery(int $productoId, ?string $from, ?string $to)
    {
        $movModel = new MovimientoInventarioModel();
        $builder = $movModel->where('producto_id', $productoId)->orderBy('created_at','ASC');
        if ($from) $builder->where('DATE(created_at) >=', $from);
        if ($to)   $builder->where('DATE(created_at) <=', $to);
        return $builder;
    }

    private function calcularTotales(array $movs): array
    {
        $entradas = 0; $salidas = 0;
        foreach ($movs as $m) {
            if ($m['tipo'] === 'entrada') $entradas += (int)$m['cantidad'];
            else                           $salidas  += (int)$m['cantidad'];
        }
        return [
            'entradas' => $entradas,
            'salidas'  => $salidas,
            'balance'  => $entradas - $salidas,
        ];
    }

    public function kardex($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->to('/productos')->with('error','Producto no encontrado');

        $stockActual = (int)($this->inventarioModel->where('producto_id',$id)->first()['stock'] ?? 0);

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $movs = $this->getKardexQuery((int)$id, $from, $to)->findAll();

        // Saldo acumulado por fila
        $saldo = 0;
        foreach ($movs as &$m) {
            $delta = ($m['tipo']==='entrada') ? (int)$m['cantidad'] : -(int)$m['cantidad'];
            $saldo += $delta;
            $m['saldo'] = $saldo;
        }
        unset($m);

        $totales = $this->calcularTotales($movs);

        return view('productos/kardex', [
            'producto'    => $producto,
            'stockActual' => $stockActual,
            'movimientos' => $movs,
            'totales'     => $totales,
            'from'        => $from,
            'to'          => $to,
            'title'       => 'Kardex - '.$producto['nombre'],
        ]);
    }

    public function exportKardex($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->response->setStatusCode(404)->setBody('Producto no encontrado');
        }

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');

        $movs = $this->getKardexQuery((int)$id, $from, $to)->findAll();

        // Prepara CSV en memoria
        $filename = 'kardex_'.$producto['sku'].'_'.date('Ymd_His').'.csv';
        $fh = fopen('php://temp', 'w');

        // Cabecera
        fputcsv($fh, ['Producto', $producto['nombre'].' ('.$producto['sku'].')']);
        fputcsv($fh, ['Rango', ($from ?: '-') . ' a ' . ($to ?: '-')]);
        fputcsv($fh, []); // línea en blanco
        fputcsv($fh, ['Fecha', 'Tipo', 'Cantidad', 'Saldo Acumulado', 'Referencia', 'Detalle']);

        // Escribe filas con saldo acumulado
        $saldo = 0;
        foreach ($movs as $m) {
            $delta = ($m['tipo']==='entrada') ? (int)$m['cantidad'] : -(int)$m['cantidad'];
            $saldo += $delta;
            fputcsv($fh, [
                date('Y-m-d H:i', strtotime($m['created_at'] ?? 'now')),
                $m['tipo'],
                (int)$m['cantidad'],
                $saldo,
                $m['referencia'] ?? '',
                $m['detalle'] ?? '',
            ]);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->setBody($csv);
    }

}