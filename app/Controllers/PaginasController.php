<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PaginasController extends BaseController
{
    public function terminos()
    {
        $data = [
            'title' => 'Políticas, Términos y Condiciones | Distribuidora Los Cheles'
        ];

        return view('paginas/politicas-terminos-y-condiciones', $data);
    }
}
