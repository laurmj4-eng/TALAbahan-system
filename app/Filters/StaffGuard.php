<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class StaffGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'staff') {
            return redirect()->to(base_url('login'))->with('error', 'Access Denied: Staff Only.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}