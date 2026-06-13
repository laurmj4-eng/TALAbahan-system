<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminGuard implements FilterInterface
{
    private const CHATBOT_PREFIXES = [
        'admin/chatbot/process',
        'admin/chatbot/deleteHistory',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $path = $request->getUri()->getPath();

        foreach (self::CHATBOT_PREFIXES as $prefix) {
            if (strpos($path, $prefix) !== false) {
                return null;
            }
        }

        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Access Denied: Admins Only.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}