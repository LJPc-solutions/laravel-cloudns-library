<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Exceptions;

/**
 * Authentication Exception
 * 
 * Thrown when API authentication fails. This typically occurs when:
 * - Invalid credentials are provided
 * - API access is not enabled for the account
 * - Sub-user permissions are insufficient
 * 
 * @package LJPc\ClouDNS\Exceptions
 * @author LJPC
 * @since 1.0.0
 */
class AuthenticationException extends ClouDNSException
{
    /**
     * Create a new authentication exception
     * 
     * @param string $message The error message
     * @param int $code The HTTP status code (default: 401 Unauthorized)
     * @param \Throwable|null $previous Previous exception
     * @param array<string, mixed> $context Additional context data
     */
    public function __construct(string $message = "Authentication failed", int $code = 401, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}