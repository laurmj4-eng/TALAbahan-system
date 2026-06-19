<?php

namespace App\Controllers;

class AppConfig extends BaseController
{
    public function js()
    {
        $recaptcha = config('Recaptcha');

        $config = [
            'BASE_URL'           => base_url(),
            'CSRF_TOKEN_NAME'    => csrf_token(),
            'CSRF_HASH'          => csrf_hash(),
            'RECAPTCHA_ENABLED'  => $recaptcha->isEnabled(),
            'RECAPTCHA_SITE_KEY' => $recaptcha->siteKey,
            'FIREBASE_CONFIG'    => [
                'apiKey'            => env('FIREBASE_API_KEY'),
                'authDomain'        => env('FIREBASE_AUTH_DOMAIN'),
                'projectId'         => env('FIREBASE_PROJECT_ID'),
                'storageBucket'     => env('FIREBASE_STORAGE_BUCKET'),
                'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'),
                'appId'             => env('FIREBASE_APP_ID'),
                'measurementId'     => env('FIREBASE_MEASUREMENT_ID'),
            ],
        ];

        $js = 'window.BASE_URL=' . json_encode($config['BASE_URL']) . ';'
            . 'window.CSRF_TOKEN_NAME=' . json_encode($config['CSRF_TOKEN_NAME']) . ';'
            . 'window.CSRF_HASH=' . json_encode($config['CSRF_HASH']) . ';'
            . 'window.RECAPTCHA_ENABLED=' . json_encode($config['RECAPTCHA_ENABLED']) . ';'
            . 'window.RECAPTCHA_SITE_KEY=' . json_encode($config['RECAPTCHA_SITE_KEY']) . ';'
            . 'window.FIREBASE_CONFIG=' . json_encode($config['FIREBASE_CONFIG']) . ';';

        return $this->response
            ->setHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setBody($js);
    }
}
