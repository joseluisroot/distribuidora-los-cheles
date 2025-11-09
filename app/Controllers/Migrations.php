<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Migrations extends BaseController
{
    public function index()
    {
        // lee token de .env: MIGRATE_TOKEN=loquesea
        $token = getenv('MIGRATE_TOKEN');
        $given = $this->request->getGet('token');

        if (!$token || $given !== $token) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $migrate = \Config\Services::migrations();

        try {
            $migrate->latest();
            return $this->response->setBody('Migraciones ejecutadas exitosamente.');
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setBody('Error al ejecutar migraciones: ' . $e->getMessage());
        }
    }
}
