<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductoModel;

class CatalogoController extends BaseController
{
    public function index()
    {
        $req = service('request');

        $q        = trim((string) $req->getGet('q'));
        $sort     = (string) ($req->getGet('sort') ?? 'recientes');
        $perPage  = (int) ($req->getGet('perPage') ?? 12);
        $perPage  = max(6, min($perPage, 48));

        $model   = new ProductoModel();
        $builder = $model->select('productos.*, inventarios.stock')
            ->join('inventarios', 'inventarios.producto_id = productos.id', 'left')
            ->where('is_activo', 1);

        $prefix   = $model->db->getPrefix();
        $subThumb = "(SELECT COALESCE(pi.thumb_path, pi.path)
             FROM {$prefix}product_images pi
             WHERE pi.producto_id = {$prefix}productos.id
               AND pi.deleted_at IS NULL
             ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
             LIMIT 1)";
        $subFull  = "(SELECT pi.path
             FROM {$prefix}product_images pi
             WHERE pi.producto_id = {$prefix}productos.id
               AND pi.deleted_at IS NULL
             ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC
             LIMIT 1)";
        $builder->select("$subThumb AS imagen_thumb", false);
        $builder->select("$subFull  AS imagen_full",  false);

        if ($q !== '') {
            $builder->groupStart()
                ->like('productos.nombre', $q)
                ->orLike('productos.sku', $q)
                ->groupEnd();
        }

        // Subqueries de escalas (1,3,10) + fallback a precio_base
        $sub1  = "(SELECT pe1.precio FROM precios_escalados pe1 WHERE pe1.producto_id = productos.id AND pe1.min_cantidad <= 1  ORDER BY pe1.min_cantidad DESC LIMIT 1)";
        $sub3  = "(SELECT pe3.precio FROM precios_escalados pe3 WHERE pe3.producto_id = productos.id AND pe3.min_cantidad <= 3  ORDER BY pe3.min_cantidad DESC LIMIT 1)";
        $sub10 = "(SELECT pe10.precio FROM precios_escalados pe10 WHERE pe10.producto_id = productos.id AND pe10.min_cantidad <= 10 ORDER BY pe10.min_cantidad DESC LIMIT 1)";
        $builder->select("COALESCE($sub1, productos.precio_base)  AS precio_q1", false);
        $builder->select("COALESCE($sub3, productos.precio_base)  AS precio_q3", false);
        $builder->select("COALESCE($sub10, productos.precio_base) AS precio_q10", false);

        // Orden
        switch ($sort) {
            case 'precio_asc':  $builder->orderBy('productos.precio_base', 'ASC');  break;
            case 'precio_desc': $builder->orderBy('productos.precio_base', 'DESC'); break;
            case 'nombre_asc':  $builder->orderBy('productos.nombre', 'ASC');       break;
            case 'nombre_desc': $builder->orderBy('productos.nombre', 'DESC');      break;
            case 'stock_desc':  $builder->orderBy('inventarios.stock', 'DESC');     break;
            default:            $builder->orderBy('productos.created_at', 'DESC');  break;
        }

        $productos = $builder->paginate($perPage, 'catalogo');
        $pager     = $model->pager;

        $query = $req->getGet(); unset($query['page_catalogo']);

        return view('catalogo/index', [
            'title'     => 'Catálogo',
            'productos' => $productos,
            'pager'     => $pager,
            'q'         => $q,
            'sort'      => $sort,
            'perPage'   => $perPage,
            'query'     => $query,
        ]);
    }

    public function show(int $id)
    {
        $model = new \App\Models\ProductoModel();

        $producto = $model
            ->select('productos.*, inventarios.stock')
            ->join('inventarios','inventarios.producto_id = productos.id','left')
            ->where('productos.is_activo', 1)
            ->find($id);

        if (!$producto) {
            return redirect()->to('catalogo')->with('error','Producto no encontrado.');
        }

        // Escalas completas para mostrar tabla
        $escalas = (new \App\Models\PrecioEscaladoModel())
            ->where('producto_id', $id)
            ->orderBy('min_cantidad','ASC')
            ->findAll();

        return view('catalogo/show', [
            'title'    => $producto['nombre'].' - Catálogo',
            'producto' => $producto,
            'escalas'  => $escalas,
        ]);
    }

    public function json(int $id)
    {
        $model = new \App\Models\ProductoModel();
        $p = $model
            ->select('productos.*, inventarios.stock')
            ->join('inventarios','inventarios.producto_id = productos.id','left')
            ->where('productos.is_activo', 1)
            ->find($id);

        if (!$p) {
            return $this->response->setStatusCode(404)->setJSON(['error'=>'No encontrado']);
        }

        // Precalcular tiers 1/3/10 como en index
        $prefix = $model->db->getPrefix();
        $db = $model->db;

        $precio1 = $db->query("
        SELECT COALESCE((SELECT precio FROM {$prefix}precios_escalados 
          WHERE producto_id = ? AND min_cantidad <= 1 ORDER BY min_cantidad DESC LIMIT 1), ?) AS precio
    ", [$id, (float)$p['precio_base']])->getRow('precio');

        $precio3 = $db->query("
        SELECT COALESCE((SELECT precio FROM {$prefix}precios_escalados 
          WHERE producto_id = ? AND min_cantidad <= 3 ORDER BY min_cantidad DESC LIMIT 1), ?) AS precio
    ", [$id, (float)$p['precio_base']])->getRow('precio');

        $precio10 = $db->query("
        SELECT COALESCE((SELECT precio FROM {$prefix}precios_escalados 
          WHERE producto_id = ? AND min_cantidad <= 10 ORDER BY min_cantidad DESC LIMIT 1), ?) AS precio
    ", [$id, (float)$p['precio_base']])->getRow('precio');

        $imgs = (new \App\Models\ProductImageModel())->byProducto($id);
        $images = [];
        foreach ($imgs as $im) {
            $images[] = [
                'src'   => $im['path'], //base_url($im['path']),
                'thumb' => $im['thumb_path'] ?: $im['path'],//base_url($im['thumb_path'] ?: $im['path']),
                'alt'   => $im['alt'] ?? $p['nombre'],
                'primary' => (int)$im['is_primary'] === 1,
            ];
        }

        return $this->response->setJSON([
            'id'          => (int)$p['id'],
            'nombre'      => $p['nombre'],
            'sku'         => $p['sku'],
            'descripcion' => $p['descripcion'],
            'imagen_url'  => $p['imagen_url'] ?? null,
            'stock'       => (int)($p['stock'] ?? 0),
            'precio_base' => (float)$p['precio_base'],
            'precio_q1'   => (float)$precio1,
            'precio_q3'   => (float)$precio3,
            'precio_q10'  => (float)$precio10,
            'url'         => site_url('catalogo/'.$p['id']),
            'images' => $images,
        ]);
    }
}
