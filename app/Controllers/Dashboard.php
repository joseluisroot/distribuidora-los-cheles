<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        $user = session('user');
        return view('dashboard/index', ['user'=>$user]);
    }
}
