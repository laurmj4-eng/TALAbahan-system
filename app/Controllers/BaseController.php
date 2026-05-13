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

        // --- Custom CORS Headers for InfinityFree/Vercel compatibility ---
        // Setting COOP to unsafe-none to allow Google/Firebase auth popups to communicate and close properly
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
            
            // Handle preflight OPTIONS request
            if ($request->getMethod() === 'options') {
                $this->response->setStatusCode(200)->send();
                exit;
            }
        }
        // -----------------------------------------------------------------

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }
}
