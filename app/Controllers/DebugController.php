<?php

namespace App\Controllers;

class DebugController extends BaseController
{
    public function checkEnv()
    {
        $b64 = env('FIREBASE_ADMIN_KEY_B64');
        $b64Direct = getenv('FIREBASE_ADMIN_KEY_B64');
        $b64Server = $_SERVER['FIREBASE_ADMIN_KEY_B64'] ?? null;
        $b64EnvArr = $_ENV['FIREBASE_ADMIN_KEY_B64'] ?? null;

        $result = [
            'env()' => $b64 ? 'SET (len=' . strlen($b64) . ')' : 'NOT SET',
            'getenv()' => $b64Direct ? 'SET (len=' . strlen($b64Direct) . ')' : 'NOT SET',
            '$_SERVER' => $b64Server ? 'SET (len=' . strlen($b64Server) . ')' : 'NOT SET',
            '$_ENV' => $b64EnvArr ? 'SET (len=' . strlen($b64EnvArr) . ')' : 'NOT SET',
        ];

        // Check a few other common env vars to verify env() is working
        $result['APP_ENV'] = env('APP_ENV') ?: 'NOT SET';
        $result['CI_ENVIRONMENT'] = env('CI_ENVIRONMENT') ?: 'NOT SET';

        return $this->response->setJSON($result);
    }
}
