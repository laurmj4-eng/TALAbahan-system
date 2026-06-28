<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => \App\Filters\CorsFilter::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        
        // --- CUSTOM AUTH FILTERS ---
        'auth'          => \App\Filters\AuthGuard::class,     // Basic login check
        'guest'         => \App\Filters\GuestFilter::class,    // Redirect if already logged in
        'adminGuard'    => \App\Filters\AdminGuard::class,    // Role: Admin
        'staffGuard'    => \App\Filters\StaffGuard::class,    // Role: Staff
        'customerGuard' => \App\Filters\CustomerGuard::class, // Role: Customer
        'chatbotGuard'  => \App\Filters\ChatbotGuard::class,  // Role: Admin or Customer
        'apiAuth'       => \App\Filters\ApiAuthFilter::class,  // API role-based auth
        'rateLimit'     => \App\Filters\RateLimitFilter::class, // Request throttling
        'activityLogger' => \App\Filters\ActivityLogger::class,
    ];

    /**
     * List of special required filters.
     */
    public array $required = [
        'before' => [
            // 'cors', // Handled in index.php now for maximum compatibility
            'pagecache',
        ],
        'after' => [
            'pagecache',   
            'performance', 
            // 'toolbar',     
        ],
    ];

    /**
     * List of filter aliases that are always applied.
     */
    public array $globals = [
        'before' => [
            // Render HTTP probes must not hit ForceHTTPS (except avoids 301 loops)
            'forcehttps' => ['except' => ['health']],
            // 'honeypot',
            'csrf' => ['except' => [
                'api/auth/*',
                'api/fcm/*',
                'api/admin/fcm/*',
                'admin/chatbot/process',
                'admin/chatbot/deleteHistory',
                'api/admin/products/toggleStatus/*',
                'api/admin/products/delete',
                'api/admin/products/store',
                'api/admin/products/update',
            ]], // Auth endpoints are public; chatbot streams the response; api/admin/products
                // are AJAX-only under apiAuth (admin role) and do not return a fresh token.
                // NOTE: the non-API admin/products/* and admin/shipping/* routes each attach
                // their own ['filter' => 'csrf'] in Routes.php, so they stay protected without
                // needing a global exemption.
            // 'invalidchars',
        ],
        'after' => [
            // 'honeypot',
            'secureheaders', // X-Frame-Options, nosniff, Referrer-Policy, etc.
            'activityLogger' => ['except' => ['health']],
        ],
    ];

    /**
     * List of filter aliases that works on a particular HTTP method.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any before or after URI patterns.
     */
    public array $filters = [
        // 1. Prevent logged-in users from seeing Auth pages
        'guest' => [
            'before' => ['/', 'login', 'register', 'auth/verify', 'auth/create_account']
        ],

        // 2. Protect Admin Routes
        'adminGuard' => [
            'before' => ['admin', 'admin/*']
        ],

        // 3. Protect Staff Routes
        'staffGuard' => [
            'before' => ['staff', 'staff/*']
        ],

        // 4. Protect Customer Routes
        'customerGuard' => [
            'before' => ['customer', 'customer/*']
        ],
    ];
}