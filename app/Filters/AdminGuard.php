<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Skip chatbot routes - they have their own chatbotGuard filter
        $path = $request->getUri()->getPath();
        if (strpos($path, '/admin/chatbot/') !== false) {
            return null; // Allow chatbot routes to pass through
        }

        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to(base_url('login'))->with('error', 'Access Denied: Admins Only.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}