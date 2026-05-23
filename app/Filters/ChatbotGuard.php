<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ChatbotGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow access to both admin and customer roles
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Please log in to access the chatbot.');
        }

        $role = session()->get('role');
        if ($role !== 'admin' && $role !== 'customer') {
            // For other roles (staff, etc.), optionally allow or deny
            // For now, allow them but with limited functionality
            return null; // Allow access
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
