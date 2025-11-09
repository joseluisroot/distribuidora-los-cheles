<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Seed extends BaseController
{
    public function run()
    {
        // Seguridad por token
        $token = getenv('MIGRATE_TOKEN');
        $given = $this->request->getGet('token');
        if (!$token || $given !== $token) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        // Opcional: restringir a ciertos entornos
        $env = env('CI_ENVIRONMENT');
        if (!in_array($env, ['development', 'staging', 'production'])) {
            return $this->response->setStatusCode(403)->setBody('Env no autorizado');
        }

        $seeder = \Config\Database::seeder();

        // 1) Ejecutar un seeder por nombre (?name=UsersSeeder)
        if ($name = $this->request->getGet('name')) {
            try {
                $seeder->call($name); // p.ej. App\Database\Seeds\UsersSeeder
                return $this->response->setBody("Seeder ejecutado: {$name}");
            } catch (\Throwable $e) {
                return $this->response->setStatusCode(500)->setBody("Error: ".$e->getMessage());
            }
        }

        /*// 2) Ejecutar un "MasterSeeder" que llame a todos
        try {
            $seeder->call('App\Database\Seeds\MasterSeeder');
            return $this->response->setBody('Seeders ejecutados (MasterSeeder).');
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setBody("Error: ".$e->getMessage());
        }*/
    }
}
