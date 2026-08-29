<?php
namespace App\Filters;
use App\Services\AccessService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!(new AccessService())->sessionUser()) {
            session()->remove(['user', 'user_id', 'role', 'isLoggedIn']);
            return redirect()->to(site_url('login'))->with('error', 'Inicia sesión para continuar.');
        }
        if ($arguments) {
            return \App\Services\AccessDeniedResponse::make($request);
        }
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
