<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Migrations extends BaseController
{
    public function index()
    {
        $migrate = \Config\Services::migrations();

        try {
            $migrate->latest();
            echo "Migraciones ejecutadas exitosamente.";
        } catch (\Throwable $e) {
            echo "Error al ejecutar migraciones: " . $e->getMessage();
        }
    }
}
