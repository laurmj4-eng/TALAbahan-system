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

        // Test base64 decode and JSON parse
        $value = $b64 ?: $b64Direct;
        if ($value) {
            $decoded = base64_decode($value, true);
            if ($decoded === false) {
                // Not base64 — try as raw JSON
                $result['base64_decode'] = 'FAILED — trying as raw JSON';
                $decoded = $value;
            } else {
                $result['base64_decode'] = 'OK (len=' . strlen($decoded) . ')';
            }
            $json = json_decode($decoded, true);
            if (!$json) {
                $result['json_decode'] = 'FAILED — ' . json_last_error_msg();
            } else {
                $result['json_decode'] = 'OK';
                $result['has_client_email'] = isset($json['client_email']) ? 'YES' : 'NO';
                $result['has_private_key'] = isset($json['private_key']) ? 'YES' : 'NO';
                $result['client_email'] = $json['client_email'] ?? 'missing';
                $result['private_key_len'] = isset($json['private_key']) ? strlen($json['private_key']) : 0;
            }
        } else {
            $result['decode_test'] = 'SKIPPED — no value to decode';
        }

        return $this->response->setJSON($result);
    }
}
