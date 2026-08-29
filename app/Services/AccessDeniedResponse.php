<?php
namespace App\Services;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AccessDeniedResponse
{
    public static function make(RequestInterface $request): ResponseInterface
    {
        $response = service('response')->setStatusCode(403)
            ->setHeader('Cache-Control', 'no-store');
        if (str_contains(strtolower($request->getHeaderLine('Accept')), 'application/json')
            || strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            return $response->setJSON([
                'error' => 'access_denied',
                'message' => 'No tienes permiso para realizar esta acción.',
            ]);
        }
        return $response->setContentType('text/html')->setBody(view('errors/access_denied'));
    }
}
