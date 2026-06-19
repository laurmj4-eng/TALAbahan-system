<?php

namespace App\Exceptions;

/**
 * Exception thrown by the Chatbot controller when an upstream AI provider
 * returns a retryable error (429 quota exhaustion, 503 overload, connection
 * failure). The catch block in process() uses this to trigger automatic
 * failover from Gemini → OpenRouter.
 */
class ChatbotApiException extends \RuntimeException
{
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * The HTTP status code returned by the upstream provider, or 0 if the
     * failure was at the connection level (DNS, timeout, TLS).
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Whether this error is a quota / rate-limit issue that should trigger
     * an automatic provider fallback.
     */
    public function isQuotaError(): bool
    {
        return in_array($this->statusCode, [429, 503], true);
    }

    /**
     * Whether this error is a connection-level failure (no HTTP response
     * was received at all).
     */
    public function isConnectionError(): bool
    {
        return $this->statusCode === 0;
    }
}
