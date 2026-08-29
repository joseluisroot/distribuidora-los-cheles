<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        $user = session('user');
        $access = new \App\Services\AccessService();
        return view('dashboard/index', [
            'user' => $user,
            'canManageAccess' => $access->can((int) $user['id'], 'access.manage'),
            'canManageProducts' => $access->can((int) $user['id'], 'products.manage'),
        ]);
    }
}
