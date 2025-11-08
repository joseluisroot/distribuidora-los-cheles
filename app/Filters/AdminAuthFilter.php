<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Ajusta estas llaves a tu implementación actual de login
        $isLogged   = $session->get('isLoggedIn');
        $userRole   = $session->get('role'); // ej: 'admin' | 'manager' | 'seller'

        if (!$isLogged) {
            return redirect()->to('/login')->with('error', 'Inicia sesión para continuar.');
        }

        // Permitir a 'admin' y 'manager' el acceso al dashboard
        if (!in_array($userRole, ['admin','manager'])) {
            return redirect()->to('/')->with('error', 'No tienes permisos para acceder al panel.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
