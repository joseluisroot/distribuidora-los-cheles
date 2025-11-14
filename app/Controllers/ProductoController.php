<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;
use App\Models\MovimientoInventarioModel;
use App\Models\PrecioEscaladoModel;
use App\Models\ProductImageModel;
use App\Models\ProductoModel;
use App\Services\InventarioService;
use Config\Services;

class ProductoController extends BaseController
{
    protected $productoModel;
    protected $inventarioModel;
    protected $escalaModel;

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
        $this->inventarioModel = new InventarioModel();
        $this->escalaModel = new PrecioEscaladoModel();
    }

    // Listar productos
    public function index()
    {
        $productos = $this->productoModel
            ->select('productos.*, inventarios.stock')
            ->join('inventarios', 'inventarios.producto_id=productos.id', 'left')
            ->orderBy('productos.sku', 'ASC')
            ->findAll();

        return view('productos/index', [
            'productos' => $productos,
            'title' => 'Productos'
        ]);
    }

    // Crear producto (form + guardar)
    public function crear()
    {
        if ($this->request->getMethod() === 'post') {
            // Validación
            $rules = [
                'sku' => 'required|min_length[1]|max_length[50]|is_unique[productos.sku]',
                'nombre' => 'required|min_length[2]|max_length[150]',
                'descripcion' => 'permit_empty|string',
                'precio_base' => 'required|decimal|greater_than_equal_to[0]',
                'stock' => 'permit_empty|is_natural',
                'is_activo' => 'permit_empty|in_list[0,1]',
                'imagen_url' => 'permit_empty|valid_url_strict',
                // archivos (múltiples)
                'imagenes' => 'permit_empty',
                'imagenes.*' => 'uploaded[imagenes.*]|is_image[imagenes.*]|max_size[imagenes.*,4096]|mime_in[imagenes.*,image/jpg,image/jpeg,image/png,image/webp]',
            ];

            // NOTA: si no suben archivos, 'uploaded' fallará. Para permitir "cero archivos", quitamos uploaded:
            // Reglas más flexibles (descomenta y comenta las anteriores si deseas cero-obligación)
            $rules['imagenes.*'] = 'permit_empty|is_image[imagenes.*]|max_size[imagenes.*,4096]|mime_in[imagenes.*,image/jpg,image/jpeg,image/png,image/webp]';

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Datos producto
            $data = [
                'sku' => trim($this->request->getPost('sku')),
                'nombre' => trim($this->request->getPost('nombre')),
                'descripcion' => trim((string)$this->request->getPost('descripcion')),
                'precio_base' => (float)$this->request->getPost('precio_base'),
                'is_activo' => $this->request->getPost('is_activo') ? 1 : 0,
                'imagen_url' => trim((string)$this->request->getPost('imagen_url')) ?: null,
            ];

            $db = \Config\Database::connect();
            $db->transStart();

            $id = $this->productoModel->insert($data, true);

            // Inventario inicial
            $this->inventarioModel->insert([
                'producto_id' => $id,
                'stock' => (int)($this->request->getPost('stock') ?? 0),
            ]);

            // ==== Manejo de imágenes subidas ====
            $files = $this->request->getFiles();
            $files = $files['imagenes'] ?? []; // puede ser array o null

            $imgModel = new ProductImageModel();
            if (!empty($files)) {

                // ¿Ya existen imágenes? (debería ser 0 en creación)
                $already = $imgModel->where('producto_id', $id)->countAllResults() > 0;
                $isFirst = !$already;

                // Carpeta destino
                $dir = FCPATH . 'uploads/products/' . $id . '/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);

                // Limitamos a 5 por request (coincide con vista)
                $slice = array_slice($files, 0, 5);

                foreach ($slice as $file) {
                    if (!$file->isValid() || $file->hasMoved()) continue;

                    // Seguridad por mimetype
                    $mime = $file->getClientMimeType();
                    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) continue;

                    // Nombre único
                    $ext = $file->getExtension();
                    $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                    $path = $dir . $name;

                    // Mover original
                    $file->move($dir, $name, true);

                    // Generar thumb 160x160 y redimensionar original a máx. 1280
                    $image = Services::image();

                    // Thumb
                    $thumbName = 'thumb_' . $name;
                    $thumbPath = $dir . $thumbName;
                    try {
                        $image->withFile($path)->fit(160, 160, 'center')->save($thumbPath, 85);
                    } catch (\Throwable $e) {
                        // si falla, no rompemos el flujo
                        $thumbPath = null;
                    }

                    // Resize original (máx. 1280 en su lado mayor)
                    try {
                        $image->withFile($path)->resize(1280, 1280, true, 'auto')->save($path, 85);
                    } catch (\Throwable $e) {
                        // si falla, dejamos el original
                    }

                    // Rutas web
                    $webPath = 'uploads/products/' . $id . '/' . $name;
                    $webThumb = $thumbPath ? 'uploads/products/' . $id . '/' . $thumbName : null;

                    // Insert en product_images
                    $imgModel->insert([
                        'producto_id' => $id,
                        'path' => $webPath,
                        'thumb_path' => $webThumb,
                        'alt' => $data['nombre'],
                        'sort_order' => 0,
                        'is_primary' => $isFirst ? 1 : 0,
                    ]);

                    // solo la primera como principal
                    if ($isFirst) $isFirst = false;
                }
            }

            // ==== /Manejo de imágenes subidas ====

            // ¿cuántas imágenes quedaron para este producto?
            $imgCount = (int)$imgModel->where('producto_id', $id)->countAllResults();

            if ($imgCount === 0 && !empty($data['imagen_url'])) {
                // Guardamos la URL remota como path; thumb_path puede ser null (tu vista ya hace fallback)
                $imgModel->insert([
                    'producto_id' => $id,
                    'path' => $data['imagen_url'],
                    'thumb_path' => null,
                    'alt' => $data['nombre'],
                    'sort_order' => 0,
                    'is_primary' => 1,
                ]);
            }
            // ==== /URL remota a product_images ====


            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('errors', ['No se pudo guardar el producto.']);
            }

            return redirect()->to('/productos')->with('message', 'Producto creado');
        }

        return view('productos/crear', ['title' => 'Nuevo producto']);
    }

    // Editar producto
    public function editar($id)
    {
        $producto  = $this->productoModel->find($id);
        $inventario= $this->inventarioModel->where('producto_id', $id)->first();

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'sku'         => 'required|min_length[1]|max_length[50]|is_unique[productos.sku,id,{id}]',
                'nombre'      => 'required|min_length[2]|max_length[150]',
                'descripcion' => 'permit_empty|string',
                'precio_base' => 'required|decimal|greater_than_equal_to[0]',
                'stock'       => 'permit_empty|is_natural',
                'is_activo'   => 'permit_empty|in_list[0,1]',
                'imagen_url'  => 'permit_empty|valid_url_strict',
            ];
            // Nota: {id} en is_unique requiere setRule('id',$id) o strtr:
            $rules['sku'] = "required|min_length[1]|max_length[50]|is_unique[productos.sku,id,{$id}]";

            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'sku'         => trim($this->request->getPost('sku')),
                'nombre'      => trim($this->request->getPost('nombre')),
                'descripcion' => trim((string)$this->request->getPost('descripcion')),
                'precio_base' => (float)$this->request->getPost('precio_base'),
                'is_activo'   => $this->request->getPost('is_activo') ? 1 : 0,
                'imagen_url'  => trim((string)$this->request->getPost('imagen_url')) ?: null,
            ];
            $this->productoModel->update($id, $data);

            $this->inventarioModel->where('producto_id', $id)
                ->set(['stock' => (int)($this->request->getPost('stock') ?? 0)])
                ->update();

            return redirect()->to('/productos')->with('message', 'Producto actualizado');
        }

        return view('productos/editar', [
            'producto'   => $producto,
            'inventario' => $inventario,
            'title'      => 'Editar producto'
        ]);
    }


    // Escalas de precios
    public function escalas($id)
    {
        $producto = $this->productoModel->find($id);
        $escalas = $this->escalaModel->where('producto_id', $id)->orderBy('min_cantidad')->findAll();

        if ($this->request->getMethod() === 'post') {
            $this->escalaModel->insert([
                'producto_id' => $id,
                'min_cantidad' => $this->request->getPost('min_cantidad'),
                'precio' => $this->request->getPost('precio'),
            ]);
            return redirect()->to('/productos/escalas/' . $id)->with('message', 'Escala agregada');
        }

        return view('productos/escalas', [
            'producto' => $producto,
            'escalas' => $escalas,
            'title' => 'Escalas de precios'
        ]);
    }

    // Eliminar producto (soft delete)
    public function eliminar($id)
    {
        $this->productoModel->delete($id);
        return redirect()->to('/productos')->with('message', 'Producto eliminado');
    }

    // Ver Kardex + filtro fechas
    public function kardex($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->to('/productos')->with('error', 'Producto no encontrado');

        $stockActual = (int)($this->inventarioModel->where('producto_id', $id)->first()['stock'] ?? 0);
        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');

        $movs = $this->getKardexQuery((int)$id, $from, $to)->findAll();

        $saldo = 0;
        foreach ($movs as &$m) {
            $delta = ($m['tipo'] === 'entrada') ? (int)$m['cantidad'] : -(int)$m['cantidad'];
            $saldo += $delta;
            $m['saldo'] = $saldo;
        }
        unset($m);

        $totales = $this->calcularTotales($movs);

        return view('productos/kardex', [
            'producto' => $producto,
            'stockActual' => $stockActual,
            'movimientos' => $movs,
            'totales' => $totales,
            'from' => $from,
            'to' => $to,
            'title' => 'Kardex - ' . $producto['nombre'],
        ]);
    }

    // Alta manual de movimiento (entrada/salida)
    public function movimiento($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->back()->with('error', 'Producto no encontrado');

        $tipo = $this->request->getPost('tipo');        // entrada|salida
        $cantidad = (int)$this->request->getPost('cantidad');
        $referencia = trim((string)$this->request->getPost('referencia'));
        $detalle = trim((string)$this->request->getPost('detalle'));

        if (!in_array($tipo, ['entrada', 'salida']) || $cantidad < 1) {
            return redirect()->back()->with('error', 'Datos de movimiento inválidos.');
        }

        try {
            $svc = new InventarioService();
            $svc->ajustar((int)$id, $cantidad, $tipo, $referencia ?: 'AJUSTE', $detalle ?: 'Ajuste manual');
            return redirect()->to('/productos/kardex/' . $id)->with('message', 'Movimiento registrado.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function getKardexQuery(int $productoId, ?string $from, ?string $to)
    {
        $movModel = new MovimientoInventarioModel();
        $builder = $movModel->where('producto_id', $productoId)->orderBy('created_at', 'ASC');
        if ($from) $builder->where('DATE(created_at) >=', $from);
        if ($to) $builder->where('DATE(created_at) <=', $to);
        return $builder;
    }

    private function calcularTotales(array $movs): array
    {
        $entradas = 0;
        $salidas = 0;
        foreach ($movs as $m) {
            if ($m['tipo'] === 'entrada') $entradas += (int)$m['cantidad'];
            else                           $salidas += (int)$m['cantidad'];
        }
        return [
            'entradas' => $entradas,
            'salidas' => $salidas,
            'balance' => $entradas - $salidas,
        ];
    }


    public function exportKardex($id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->response->setStatusCode(404)->setBody('Producto no encontrado');
        }

        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');

        $movs = $this->getKardexQuery((int)$id, $from, $to)->findAll();

        // Prepara CSV en memoria
        $filename = 'kardex_' . $producto['sku'] . '_' . date('Ymd_His') . '.csv';
        $fh = fopen('php://temp', 'w');

        // Cabecera
        fputcsv($fh, ['Producto', $producto['nombre'] . ' (' . $producto['sku'] . ')']);
        fputcsv($fh, ['Rango', ($from ?: '-') . ' a ' . ($to ?: '-')]);
        fputcsv($fh, []); // línea en blanco
        fputcsv($fh, ['Fecha', 'Tipo', 'Cantidad', 'Saldo Acumulado', 'Referencia', 'Detalle']);

        // Escribe filas con saldo acumulado
        $saldo = 0;
        foreach ($movs as $m) {
            $delta = ($m['tipo'] === 'entrada') ? (int)$m['cantidad'] : -(int)$m['cantidad'];
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
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function imagenes(int $id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->to('/productos')->with('error', 'Producto no encontrado');

        $imgs = (new ProductImageModel())->byProducto($id);

        return view('productos/imagenes', [
            'title' => 'Imágenes - ' . $producto['nombre'],
            'producto' => $producto,
            'imagenes' => $imgs,
        ]);
    }

    public function subirImagen(int $id)
    {
        $producto = $this->productoModel->find($id);
        if (!$producto) return redirect()->back()->with('error', 'Producto no encontrado');

        $file = $this->request->getFile('imagen');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Archivo inválido.');
        }

        // Validaciones
        if (!in_array($file->getClientMimeType(), ['image/jpeg', 'image/png', 'image/webp'])) {
            return redirect()->back()->with('error', 'Formato no permitido. Usa JPG/PNG/WEBP.');
        }
        if ($file->getSize() > 4 * 1024 * 1024) { // 4MB
            return redirect()->back()->with('error', 'La imagen supera 4MB.');
        }

        // Carpeta destino
        $dir = FCPATH . 'uploads/products/' . $id . '/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        // Nombre único
        $ext = $file->getExtension(); // respeta extension original
        $name = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $path = $dir . $name;

        // Mover original
        $file->move($dir, $name, true);

        // Generar thumb (160x160) y opcionalmente grande (max 1280)
        $image = Services::image();

        // Thumb
        $thumbName = 'thumb_' . $name;
        $thumbPath = $dir . $thumbName;
        $image->withFile($path)->fit(160, 160, 'center')->save($thumbPath, 85);

        // (Opcional) redimensionar original si es muy grande
        try {
            $image->withFile($path)->resize(1280, 1280, true, 'auto')->save($path, 85);
        } catch (\Throwable $e) {
            // si falla resize, deja el original
        }

        $webPath = 'uploads/products/' . $id . '/' . $name;
        $webThumb = 'uploads/products/' . $id . '/' . $thumbName;

        $imgModel = new ProductImageModel();
        $isFirst = !$imgModel->where('producto_id', $id)->countAllResults(); // si es la primera, será principal

        $imgModel->insert([
            'producto_id' => $id,
            'path' => $webPath,
            'thumb_path' => $webThumb,
            'alt' => $producto['nombre'],
            'sort_order' => 0,
            'is_primary' => $isFirst ? 1 : 0,
        ]);

        return redirect()->to('/productos/' . $id . '/imagenes')->with('message', 'Imagen subida.');
    }

    public function imagenPrincipal(int $id, int $imageId)
    {
        (new ProductImageModel())->setPrimary($id, $imageId);
        return redirect()->back()->with('message', 'Imagen principal actualizada.');
    }

    public function eliminarImagen(int $id, int $imageId)
    {
        $imgModel = new ProductImageModel();
        $img = $imgModel->find($imageId);
        if ($img && (int)$img['producto_id'] === $id) {
            @unlink(FCPATH . $img['path']);
            if (!empty($img['thumb_path'])) @unlink(FCPATH . $img['thumb_path']);
            $imgModel->delete($imageId);
        }
        return redirect()->back()->with('message', 'Imagen eliminada.');
    }


}