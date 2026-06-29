<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle Preflight OPTIONS request
        if ($request->getMethod() === 'options') {
            $response = service('response');
            
            $origin = $request->getHeaderLine('Origin');
            
            // Log for debugging (will show up in CI logs)
            log_message('debug', 'CORS Preflight request from Origin: ' . $origin);

            $allowedOrigins = [
                'https://tal-abahan-system.vercel.app',
                'http://localhost:5173',
                'http://localhost:8080'
            ];

            if (in_array($origin, $allowedOrigins)) {
                $response->setHeader('Access-Control-Allow-Origin', $origin);
                $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
                $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Auth-Token, Accept, Origin, X-API-KEY');
                $response->setHeader('Access-Control-Allow-Credentials', 'true');
                $response->setHeader('Access-Control-Max-Age', '86400');
                $response->setStatusCode(200);
            } else {
                $response->setStatusCode(204);
            }
            
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = [
            'https://tal-abahan-system.vercel.app',
            'http://localhost:5173',
            'http://localhost:8080'
        ];

        if (in_array($origin, $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
            $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Auth-Token, Accept, Origin, X-API-KEY');
            $response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
