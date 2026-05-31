<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class Inertia
{
    protected static $sharedData = [];
    protected static $rootView = 'app';
    protected static $version = null;

    /**
     * Share data that will be available to all Inertia components
     */
    public static function share($key, $value = null)
    {
        if (is_array($key)) {
            static::$sharedData = array_merge(static::$sharedData, $key);
        } else {
            static::$sharedData[$key] = $value;
        }
    }

    public static function getShared($key = null)
    {
        if ($key) {
            return static::$sharedData[$key] ?? null;
        }
        return static::$sharedData;
    }

    public static function setRootView($view)
    {
        static::$rootView = $view;
    }

    /**
     * Set asset version for cache busting
     * In production, use: Inertia::version(env('INERTIA_VERSION'))
     * Or use git commit hash: Inertia::version(shell_exec('git rev-parse HEAD'))
     */
    public static function version($version)
    {
        static::$version = $version;
    }

    /**
     * Get or auto-detect version from environment
     */
    public static function getVersion()
    {
        if (static::$version) {
            return static::$version;
        }

        // Auto-detect from environment in production
        if (ENVIRONMENT === 'production') {
            $manifest = FCPATH . 'build/manifest.json';
            if (! is_file($manifest)) {
                $manifest = FCPATH . 'public/build/manifest.json';
            }

            return env('INERTIA_VERSION', is_file($manifest) ? hash('crc32', (string) filemtime($manifest)) : '1');
        }

        // Development: use timestamp for frequent updates
        return time();
    }

    /**
     * Render Inertia component with proper asset versioning
     */
    public static function render($component, $props = [])
    {
        $request = service('request');
        $response = service('response');

        // Ensure props is an object even if empty
        $props = (object) array_merge(static::$sharedData, $props);

        $page = [
            'component' => $component,
            'props'     => $props,
            'url'       => (string) $request->getUri(),
            'version'   => static::getVersion(),
        ];

        // Diagnostic log
        log_message('debug', '[Inertia] Rendering component: ' . $component . ' (version: ' . $page['version'] . ')');

        // If it's an Inertia XHR request, return JSON
        if ($request->getHeaderLine('X-Inertia')) {
            return $response
                ->setHeader('X-Inertia', 'true')
                ->setHeader('X-Inertia-Version', $page['version'])
                ->setHeader('Vary', 'Accept')
                ->setJSON($page);
        }

        // Otherwise, render the HTML template
        return view(static::$rootView, ['page' => $page]);
    }
}
