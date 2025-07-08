<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Exceptions;

/**
 * Rate Limit Exception
 * 
 * Thrown when API rate limits are exceeded. ClouDNS enforces:
 * - 20 requests per second
 * - 600 requests per minute
 * 
 * The client will automatically retry with exponential backoff.
 * 
 * @package LJPc\ClouDNS\Exceptions
 * @author LJPC
 * @since 1.0.0
 */
class RateLimitException extends ClouDNSException
{
    /**
     * @var int|null Time to wait before retrying (in seconds)
     */
    protected ?int $retryAfter = null;

    /**
     * Create a new rate limit exception
     * 
     * @param string $message The error message
     * @param int $code The HTTP status code (default: 429 Too Many Requests)
     * @param \Throwable|null $previous Previous exception
     * @param array<string, mixed> $context Additional context data
     * @param int|null $retryAfter Seconds to wait before retrying
     */
    public function __construct(
        string $message = "Rate limit exceeded", 
        int $code = 429, 
        ?\Throwable $previous = null, 
        array $context = [],
        ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the retry after time
     * 
     * @return int|null Seconds to wait before retrying
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Set the retry after time
     * 
     * @param int|null $retryAfter Seconds to wait before retrying
     * @return self For method chaining
     */
    public function setRetryAfter(?int $retryAfter): self
    {
        $this->retryAfter = $retryAfter;
        return $this;
    }
}