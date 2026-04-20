<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('role');
            if ($role === 'admin') return redirect()->to(base_url('admin/dashboard'));
            if ($role === 'staff') return redirect()->to(base_url('staff/dashboard'));
            return redirect()->to(base_url('customer/dashboard'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}