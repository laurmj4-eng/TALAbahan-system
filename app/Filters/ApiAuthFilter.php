<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiAuthFilter implements FilterInterface
{
    private const ROLE_MAP = [
        'admin'   => 'admin',
        'staff'   => 'staff',
        'customer'=> 'customer',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $path = $request->getUri()->getPath();

        $role = $this->detectRole($path);

        if ($role === null) {
            return null;
        }

        if (!session()->get('isLoggedIn') || session()->get('role') !== $role) {
            return $this->jsonError($request, $role);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function detectRole(string $path): ?string
    {
        foreach (self::ROLE_MAP as $prefix => $role) {
            if (strpos($path, 'api/' . $prefix) === 0) {
                return $role;
            }
        }

        return null;
    }

    private function jsonError(RequestInterface $request, string $expectedRole): ResponseInterface
    {
        $response = service('response');
        $response->setStatusCode(403);
        $response->setContentType('application/json');
        $response->setBody(json_encode([
            'status'  => 'error',
            'message' => 'Access Denied: ' . ucfirst($expectedRole) . ' authentication required.',
        ]));

        return $response;
    }
}
