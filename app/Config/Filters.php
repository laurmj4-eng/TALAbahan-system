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
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        
        // --- CUSTOM AUTH FILTERS ---
        'auth'          => \App\Filters\AuthGuard::class,     // Basic login check
        'guest'         => \App\Filters\GuestFilter::class,    // Redirect if already logged in
        'adminGuard'    => \App\Filters\AdminGuard::class,    // Role: Admin
        'staffGuard'    => \App\Filters\StaffGuard::class,    // Role: Staff
        'customerGuard' => \App\Filters\CustomerGuard::class, // Role: Customer
        'activityLogger' => \App\Filters\ActivityLogger::class,
    ];

    /**
     * List of special required filters.
     */
    public array $required = [
        'before' => [
            'forcehttps', 
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
            // 'honeypot',
            'csrf' => ['except' => ['admin/chatbot/process']], // Exclude Admin Chatbot from CSRF
            // 'invalidchars',
        ],
        'after' => [
            // 'honeypot',
            // 'secureheaders',
            'activityLogger',
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