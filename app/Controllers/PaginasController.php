<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PaginasController extends BaseController
{
    public function terminos()
    {
        $data = [
            'title' => 'Políticas, Términos y Condiciones | Distribuidora Los Cheles',
            'seo' => [
                'public' => true,
                'url' => site_url('politicas-terminos-y-condiciones'),
                'description' => 'Consulta las políticas de cambios, devoluciones y garantías de Distribuidora Los Cheles, así como nuestras formas de pago.',
            ],
        ];

        return view('paginas/politicas-terminos-y-condiciones', $data);
    }
}
