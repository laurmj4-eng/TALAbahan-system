<?php

/**
 * reCAPTCHA v2 helpers (checkbox / explicit render).
 * Keys: RECAPTCHA_SITE_KEY + RECAPTCHA_SECRET_KEY in .env (restart Apache after changes).
 */

if (! function_exists('recaptcha_config')) {
    function recaptcha_config(): Config\Recaptcha
    {
        return config('Recaptcha');
    }
}

if (! function_exists('is_recaptcha_enabled')) {
    function is_recaptcha_enabled(): bool
    {
        return recaptcha_config()->isEnabled();
    }
}

if (! function_exists('verify_recaptcha_response')) {
    /**
     * Verify g-recaptcha-response with Google siteverify using RECAPTCHA_SECRET_KEY.
     *
     * @return array{ok: bool, message: string, error_codes: list<string>}
     */
    function verify_recaptcha_response(?string $token): array
    {
        $config = recaptcha_config();

        if (! $config->isEnabled()) {
            return ['ok' => true, 'message' => '', 'error_codes' => []];
        }

        if ($token === null || trim($token) === '') {
            return [
                'ok'          => false,
                'message'     => 'Please complete the reCAPTCHA verification.',
                'error_codes' => ['missing-input-response'],
            ];
        }

        if ($config->secretKey === '') {
            log_message('error', '[reCAPTCHA] RECAPTCHA_SECRET_KEY is empty in .env');

            return [
                'ok'          => false,
                'message'     => 'reCAPTCHA is not configured on the server.',
                'error_codes' => ['missing-secret'],
            ];
        }

        $payload = [
            'secret'   => $config->secretKey,
            'response' => trim($token),
        ];

        $request = service('request');
        $remoteIp = $request->getIPAddress();
        if ($remoteIp !== '' && $remoteIp !== '0.0.0.0') {
            $payload['remoteip'] = $remoteIp;
        }

        try {
            $client = service('curlrequest', [
                'verify'  => $config->sslVerify,
                'timeout' => 10,
            ]);

            $httpResponse = $client->post($config->verifyUrl, [
                'form_params' => $payload,
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[reCAPTCHA] siteverify request failed: ' . $e->getMessage());

            return [
                'ok'          => false,
                'message'     => 'reCAPTCHA verification is temporarily unavailable. Please try again.',
                'error_codes' => ['network-error'],
            ];
        }

        $status = $httpResponse->getStatusCode();
        $body   = (string) $httpResponse->getBody();

        if ($status !== 200) {
            log_message('error', '[reCAPTCHA] siteverify HTTP ' . $status . ': ' . $body);

            return [
                'ok'          => false,
                'message'     => 'reCAPTCHA verification is temporarily unavailable. Please try again.',
                'error_codes' => ['http-' . $status],
            ];
        }

        $captchaData = json_decode($body);
        if (! $captchaData || ! ($captchaData->success ?? false)) {
            $codes = [];
            if (isset($captchaData->{'error-codes'}) && is_array($captchaData->{'error-codes'})) {
                $codes = $captchaData->{'error-codes'};
            }

            log_message('warning', '[reCAPTCHA] siteverify failed: ' . implode(', ', $codes));

            $message = 'reCAPTCHA verification failed. Please try again.';
            if (in_array('invalid-input-secret', $codes, true) || in_array('invalid-keys', $codes, true)) {
                $message = 'reCAPTCHA secret key is invalid. Update RECAPTCHA_SECRET_KEY in .env to match your site key pair, then restart Apache.';
            } elseif (in_array('timeout-or-duplicate', $codes, true)) {
                $message = 'reCAPTCHA expired. Please check the box again.';
            } elseif (in_array('browser-error', $codes, true) || in_array('invalid-input-response', $codes, true)) {
                $message = 'reCAPTCHA could not be verified for this page. Add localhost and 127.0.0.1 to your key domains in Google reCAPTCHA Admin.';
            }

            return [
                'ok'          => false,
                'message'     => $message,
                'error_codes' => $codes,
            ];
        }

        return ['ok' => true, 'message' => '', 'error_codes' => []];
    }
}

if (! function_exists('recaptcha_json_error')) {
    /**
     * Build a JSON error response when verification fails (for API routes).
     */
    function recaptcha_json_error(array $captcha): \CodeIgniter\HTTP\ResponseInterface
    {
        return service('response')
            ->setJSON([
                'status'  => 'error',
                'message' => $captcha['message'],
                'token'   => csrf_hash(),
            ])
            ->setStatusCode(400);
    }
}
