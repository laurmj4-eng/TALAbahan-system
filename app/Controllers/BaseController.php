<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['form', 'url', 'inertia'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // --- CORS Headers ---
        // COOP set to unsafe-none for Firebase auth popup compatibility
        $this->response->setHeader('Cross-Origin-Opener-Policy', 'unsafe-none');
        $this->response->setHeader('Cross-Origin-Embedder-Policy', 'unsafe-none');
        
        $origin = $request->getHeaderLine('Origin');
        $allowedOrigins = [
            'https://tal-abahan-system.vercel.app',
            'http://localhost:5173',
            'http://localhost:8080'
        ];

        if (in_array($origin, $allowedOrigins)) {
            $this->response->setHeader('Access-Control-Allow-Origin', $origin);
            $this->response->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
            $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
            $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        }

        // Handle preflight OPTIONS request
        if ($request->getMethod() === 'options') {
            $this->response->setStatusCode(200)->send();
            exit;
        }

        // --- Content Security Policy (manual — CI4's built-in CSP adds nonces that break unsafe-inline) ---
        $this->response->setHeader('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.gstatic.com https://accounts.google.com https://apis.google.com https://www.google.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self' https://identitytoolkit.googleapis.com https://securetoken.googleapis.com https://www.googleapis.com https://www.gstatic.com; font-src 'self' data:; frame-src https://accounts.google.com https://apis.google.com https://sefood-d603d.firebaseapp.com https://www.google.com; object-src 'none'; worker-src 'self'; form-action 'self';");
        // -----------------------------------------------------------------

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');

        // Send CSRF token in every response so JavaScript can update the meta tag
        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());
    }
}
