<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Lightweight liveness probe for load balancers (Render, Docker, etc.).
 * Does not touch the database or session.
 */
class Health extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/json')
            ->setJSON([
                'status'    => 'healthy',
                'timestamp' => date('c'),
            ]);
    }
}
