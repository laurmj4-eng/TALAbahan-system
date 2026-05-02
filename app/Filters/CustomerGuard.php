<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CustomerGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'customer') {
            return redirect()->to(base_url('login'))->with('error', 'Access Denied: Customers Only.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}