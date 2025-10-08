<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Auth::login');
$routes->get('/', 'CatalogoController::index');

$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::doLogin');
$routes->get('logout', 'Auth::logout');

$routes->get('forgot', 'Auth::forgot');
$routes->post('forgot', 'Auth::sendReset');
$routes->get('reset/(:segment)', 'Auth::reset/$1');
$routes->post('reset', 'Auth::doReset');
$routes->get('register', 'Auth::register');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // Ejemplos (futuros módulos):
    // $routes->group('admin', ['filter'=>'auth:admin'], function($routes){
    //   $routes->get('productos', 'Admin\Productos::index');
    // });
});

// catálogo simple para probar (listar productos activos)
$routes->get('catalogo', 'CatalogoController::index');
// Catálogo (visible para usuarios logueados o público, tú decides)
$routes->get('catalogo', 'CarretillaController::catalogo');
// PDP (detalle de producto)
$routes->get('catalogo/(:num)', 'CatalogoController::show/$1');
// Endpoint JSON para Vista Rápida (modal)
$routes->get('catalogo/json/(:num)', 'CatalogoController::json/$1');

$routes->post('carretilla/agregar', 'CarretillaController::agregar');
$routes->get('carretilla', 'CarretillaController::index'); // opcional


// endpoints de prueba de pedido
$routes->group('demo', function ($r) {
    // crear pedido vacío para cliente 2
    $r->get('crear-pedido', function () {
        $svc = new \App\Services\PedidoService();
        $pedido = $svc->crearPedido(2, 'Pedido demo');
        return json_encode($pedido);
    });

    // agregar item: ?pedido=1&producto=1&cant=3
    $r->get('agregar-item', function () {
        $svc = new \App\Services\PedidoService();
        $pedidoId = (int)($_GET['pedido'] ?? 0);
        $prodId = (int)($_GET['producto'] ?? 0);
        $cant = (int)($_GET['cant'] ?? 1);
        $svc->agregarItem($pedidoId, $prodId, $cant);
        return 'OK';
    });

    // confirmar (cambia a preparando y descuenta stock)
    // ?pedido=1&user=1
    $r->get('confirmar', function () {
        $svc = new \App\Services\PedidoService();
        $pedidoId = (int)($_GET['pedido'] ?? 0);
        $userId = (int)($_GET['user'] ?? 1);
        $svc->confirmar($pedidoId, $userId);
        return 'OK';
    });

    // procesar (cambia a procesado)
    // ?pedido=1&user=1
    $r->get('procesar', function () {
        $svc = new \App\Services\PedidoService();
        $pedidoId = (int)($_GET['pedido'] ?? 0);
        $userId = (int)($_GET['user'] ?? 1);
        $svc->procesar($pedidoId, $userId, 'Entrega completa');
        return 'OK';
    });
});



// Carretilla y Checkout (requieren login)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('carretilla', 'CarretillaController::index');
    $routes->post('carretilla/add', 'CarretillaController::add');
    $routes->post('carretilla/update', 'CarretillaController::update');
    $routes->get('carretilla/remove/(:num)', 'CarretillaController::remove/$1');
    $routes->get('carretilla/clear', 'CarretillaController::clear');

    $routes->get('carretilla/checkout', 'CarretillaController::checkout');
    $routes->post('carretilla/place-order', 'CarretillaController::placeOrder');
});


$routes->group('productos', ['filter' => 'auth:admin'], function ($routes) {
    $routes->get('/', 'ProductoController::index');
    $routes->match(['get', 'post'], 'crear', 'ProductoController::crear');
    $routes->match(['get', 'post'], 'editar/(:num)', 'ProductoController::editar/$1');
    $routes->match(['get', 'post'], 'escalas/(:num)', 'ProductoController::escalas/$1');
    $routes->get('eliminar/(:num)', 'ProductoController::eliminar/$1');

    // Kardex
    $routes->get('kardex/(:num)', 'ProductoController::kardex/$1');
    $routes->post('kardex/(:num)/movimiento', 'ProductoController::movimiento/$1');

    // Export CSV
    $routes->get('kardex/(:num)/export', 'ProductoController::exportKardex/$1');

    $routes->get('reporte', 'PedidoController::reporte');
    $routes->get('reporte/export', 'PedidoController::exportReporte');
});

$routes->get('productos/(:num)/imagenes', 'ProductoController::imagenes/$1');
$routes->post('productos/(:num)/imagenes/subir', 'ProductoController::subirImagen/$1');
$routes->post('productos/(:num)/imagenes/(:num)/principal', 'ProductoController::imagenPrincipal/$1/$2');
$routes->post('productos/(:num)/imagenes/(:num)/eliminar', 'ProductoController::eliminarImagen/$1/$2');


$routes->group('pedidos', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PedidoController::index');
    $routes->get('(:num)', 'PedidoController::ver/$1');

    $routes->post('cambiar-estado/(:num)', 'PedidoController::cambiarEstado/$1');
});


