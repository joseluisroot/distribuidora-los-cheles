<?php
namespace App\Filters;
class AdminAuthFilter extends PermissionFilter
{
    public function before(\CodeIgniter\HTTP\RequestInterface $request, $arguments = null)
    {
        return parent::before($request, ['dashboard.view']);
    }
}
