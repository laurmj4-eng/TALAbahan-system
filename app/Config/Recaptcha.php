<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Recaptcha extends BaseConfig
{
    /** Google reCAPTCHA v2 site key (public, used in the browser). */
    public string $siteKey = '';

    /** Google reCAPTCHA v2 secret key (server-only, from .env). */
    public string $secretKey = '';

    /** When true, widget and server verification are skipped. */
    public bool $disabled = false;

    /** Standard v2 siteverify endpoint. */
    public string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /** Set false only if PHP/cURL cannot verify Google SSL certificates (rare on XAMPP). */
    public bool $sslVerify = true;

    public function __construct()
    {
        parent::__construct();

        $this->siteKey   = self::normalizeEnv(env('RECAPTCHA_SITE_KEY'));
        $this->secretKey = self::normalizeEnv(env('RECAPTCHA_SECRET_KEY'));

        $disabled = env('RECAPTCHA_DISABLED');
        if ($disabled !== null && $disabled !== '') {
            $this->disabled = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        }

        $sslVerify = env('RECAPTCHA_SSL_VERIFY');
        if ($sslVerify !== null && $sslVerify !== '') {
            $this->sslVerify = filter_var($sslVerify, FILTER_VALIDATE_BOOLEAN);
        }
    }

    public function isEnabled(): bool
    {
        if ($this->disabled) {
            return false;
        }

        return $this->siteKey !== '' && $this->secretKey !== '';
    }

    private static function normalizeEnv(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (
            ($value[0] === '"' && str_ends_with($value, '"'))
            || ($value[0] === "'" && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        return trim($value);
    }
}
