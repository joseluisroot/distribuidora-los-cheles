<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventarioModel;
use App\Models\ProductoModel;
use App\Services\PedidoService;

class CarretillaController extends BaseController
{
    protected ProductoModel $productos;
    protected InventarioModel $inventario;

    public function __construct()
    {
        $this->productos  = new ProductoModel();
        $this->inventario = new InventarioModel();
    }

    /** Mostrar catálogo rápido (cliente) */
    public function catalogo()
    {
        $items = $this->productos
            ->select('productos.*, inventarios.stock')
            ->join('inventarios','inventarios.producto_id = productos.id','left')
            ->where('is_activo',1)
            ->orderBy('nombre','ASC')->findAll();

        return view('catalogo/index', ['productos'=>$items, 'title'=>'Catálogo']);
    }

    /** Ver carretilla */
    public function index()
    {
        $cart = session('cart') ?? []; // [producto_id => cantidad]
        $lineas = [];
        $total = 0;

        foreach ($cart as $pid => $cant) {
            $p = $this->productos->findActivo((int)$pid);
            if (!$p) continue;

            $precioUnit = (float)precio_por_cantidad((int)$pid, (int)$cant);
            $subtotal   = round($precioUnit * (int)$cant, 2, PHP_ROUND_HALF_UP);
            $stock      = $this->inventario->getStockDeProducto((int)$pid);

            $lineas[] = [
                'id'        => (int)$pid,
                'sku'       => $p['sku'],
                'nombre'    => $p['nombre'],
                'precio'    => $precioUnit,
                'cantidad'  => (int)$cant,
                'subtotal'  => $subtotal,
                'stock'     => $stock,
            ];
            $total += $subtotal;
        }

        return view('carretilla/index', [
            'items' => $lineas,
            'total' => round($total,2, PHP_ROUND_HALF_UP),
            'title' => 'Mi Carretilla'
        ]);
    }

    /** Agregar al carrito (POST) */
    public function add()
    {
        $pid  = (int)$this->request->getPost('producto_id');
        $cant = max(1, (int)$this->request->getPost('cantidad'));

        // Validar producto activo
        $p = $this->productos->findActivo($pid);
        if (!$p) return redirect()->back()->with('error','Producto no disponible.');

        $cart = session('cart') ?? [];
        $cart[$pid] = ($cart[$pid] ?? 0) + $cant;

        session()->set('cart', $cart);
        return redirect()->to('/carretilla')->with('message','Producto agregado a la carretilla.');
    }

    /** Actualizar cantidad (POST) */
    public function update()
    {
        $pid  = (int)$this->request->getPost('producto_id');
        $cant = (int)$this->request->getPost('cantidad');

        $cart = session('cart') ?? [];
        if (!isset($cart[$pid])) return redirect()->back();

        if ($cant <= 0) {
            unset($cart[$pid]);
        } else {
            $cart[$pid] = $cant;
        }
        session()->set('cart', $cart);
        return redirect()->to('/carretilla')->with('message','Carretilla actualizada.');
    }

    /** Eliminar ítem */
    public function remove($pid)
    {
        $pid = (int)$pid;
        $cart = session('cart') ?? [];
        unset($cart[$pid]);
        session()->set('cart', $cart);
        return redirect()->to('/carretilla')->with('message','Producto eliminado.');
    }

    /** Vaciar carretilla */
    public function clear()
    {
        session()->remove('cart');
        return redirect()->to('/carretilla')->with('message','Carretilla vaciada.');
    }

    /** Pantalla de checkout */
    public function checkout()
    {
        $user = session('user');
        if (!$user) return redirect()->to('/login')->with('error','Inicia sesión para continuar.');

        $cart = session('cart') ?? [];
        if (!$cart) return redirect()->to('/catalogo')->with('error','Tu carretilla está vacía.');

        // Recalcular precios y validar stock
        $items = [];
        $total = 0;
        $erroresStock = [];

        foreach ($cart as $pid => $cant) {
            $p = $this->productos->findActivo((int)$pid);
            if (!$p) continue;

            $precioUnit = (float)precio_por_cantidad((int)$pid, (int)$cant);
            $subtotal   = round($precioUnit * (int)$cant, 2, PHP_ROUND_HALF_UP);
            $stock      = $this->inventario->getStockDeProducto((int)$pid);

            if ($cant > $stock) {
                $erroresStock[] = "Stock insuficiente de {$p['nombre']} (solicitado {$cant}, disponible {$stock}).";
            }

            $items[] = [
                'id'        => (int)$pid,
                'sku'       => $p['sku'],
                'nombre'    => $p['nombre'],
                'precio'    => $precioUnit,
                'cantidad'  => (int)$cant,
                'subtotal'  => $subtotal,
                'stock'     => $stock,
            ];
            $total += $subtotal;
        }

        return view('carretilla/checkout', [
            'items'   => $items,
            'total'   => round($total,2, PHP_ROUND_HALF_UP),
            'errores' => $erroresStock,
            'title'   => 'Confirmar Pedido'
        ]);
    }

    /** Confirmar pedido: crea pedido, agrega ítems, confirma (descuenta inventario) */
    public function placeOrder()
    {
        $user = session('user');
        if (!$user) return redirect()->to('/login')->with('error','Inicia sesión para continuar.');

        $cart = session('cart') ?? [];
        if (!$cart) return redirect()->to('/catalogo')->with('error','Tu carretilla está vacía.');

        // Revalidar stock
        foreach ($cart as $pid => $cant) {
            $stock = $this->inventario->getStockDeProducto((int)$pid);
            if ($cant > $stock) {
                return redirect()->to('/carretilla/checkout')
                    ->with('error',"Stock insuficiente para producto ID {$pid}.");
            }
        }

        $svc = new PedidoService();

        // Crear pedido
        $pedido = $svc->crearPedido((int)$user['id'], $this->request->getPost('observaciones'));

        // Agregar ítems con precios por escala (congelados)
        foreach ($cart as $pid => $cant) {
            $svc->agregarItem((int)$pedido['id'], (int)$pid, (int)$cant);
        }

        // Confirmar (cambia a 'preparando' y descuenta inventario)
        $svc->confirmar((int)$pedido['id'], (int)$user['id']);

        // Limpiar carretilla
        session()->remove('cart');

        return redirect()->to('/pedidos/'.$pedido['id'])
            ->with('message','Pedido creado y confirmado. Estado: preparando.');
    }

}