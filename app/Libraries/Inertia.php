<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

class Inertia
{
    protected static $sharedData = [];
    protected static $rootView = 'app';
    protected static $version = null;

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

    public static function version($version)
    {
        static::$version = $version;
    }

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
            'version'   => static::$version,
        ];

        // Diagnostic log
        log_message('debug', '[Inertia] Rendering component: ' . $component);

        if ($request->getHeaderLine('X-Inertia')) {
            return $response
                ->setHeader('X-Inertia', 'true')
                ->setHeader('Vary', 'Accept')
                ->setJSON($page);
        }

        return view(static::$rootView, ['page' => $page]);
    }
}
