<?php
namespace App\Filters;

use App\Services\AccessService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $access = new AccessService();
        $user = $access->sessionUser();
        if (!$user) {
            return redirect()->to(site_url('login'));
        }
        // Legacy screens have no warehouse isolation. Require GLOBAL grants.
        foreach ($arguments ?? [] as $permission) {
            if (!$access->can((int) $user['id'], $permission, 'global')) {
                return \App\Services\AccessDeniedResponse::make($request);
            }
        }
        if (!$arguments) {
            return \App\Services\AccessDeniedResponse::make($request);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
