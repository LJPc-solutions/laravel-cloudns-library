<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Exceptions;

use Exception;

/**
 * Base ClouDNS Exception
 * 
 * The base exception class for all ClouDNS-related errors.
 * Provides context data for better error debugging.
 * 
 * @package LJPc\ClouDNS\Exceptions
 * @author LJPC
 * @since 1.0.0
 */
class ClouDNSException extends Exception
{
    /**
     * @var array<string, mixed> Additional context data for the exception
     */
    protected array $context = [];

    /**
     * Create a new ClouDNS exception instance
     * 
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous The previous exception
     * @param array<string, mixed> $context Additional context data
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the exception context data
     * 
     * @return array<string, mixed> The context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set the exception context data
     * 
     * @param array<string, mixed> $context The context data to set
     * @return self For method chaining
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}