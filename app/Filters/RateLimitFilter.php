<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RateLimitFilter implements FilterInterface
{
    private int $maxAttempts;
    private int $windowSeconds;

    public function __construct()
    {
        $this->maxAttempts    = (int) (env('rateLimit.maxAttempts') ?: 20);
        $this->windowSeconds  = (int) (env('rateLimit.windowSeconds') ?: 60);
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $key = $this->resolveKey($request);

        $cache = cache();
        $cacheKey = 'ratelimit_' . $key;

        $attempts = (int) $cache->get($cacheKey);

        if ($attempts >= $this->maxAttempts) {
            return $this->tooManyRequests();
        }

        if ($attempts === 0) {
            $cache->save($cacheKey, 1, $this->windowSeconds);
        } else {
            $cache->save($cacheKey, $attempts + 1, $this->windowSeconds);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}

    private function resolveKey(RequestInterface $request): string
    {
        $ip   = $request->getIPAddress() ?: 'unknown';
        $path = $request->getUri()->getPath();

        return md5($ip . '_' . $path);
    }

    private function tooManyRequests(): ResponseInterface
    {
        $response = service('response');
        $response->setStatusCode(429);
        $response->setContentType('application/json');
        $response->setBody(json_encode([
            'status'  => 'error',
            'message' => 'Too many requests. Please try again later.',
        ]));

        return $response;
    }
}
