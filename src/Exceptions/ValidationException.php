<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Exceptions;

/**
 * Validation Exception
 * 
 * Thrown when API request validation fails. This occurs when:
 * - Required parameters are missing
 * - Parameter values are invalid
 * - Business rule validation fails
 * 
 * @package LJPc\ClouDNS\Exceptions
 * @author LJPC
 * @since 1.0.0
 */
class ValidationException extends ClouDNSException
{
    /**
     * @var array<string, mixed> Validation errors by field
     */
    protected array $errors = [];

    /**
     * Create a new validation exception
     * 
     * @param string $message The error message
     * @param int $code The HTTP status code (default: 422 Unprocessable Entity)
     * @param \Throwable|null $previous Previous exception
     * @param array<string, mixed> $errors Validation errors by field
     */
    public function __construct(string $message = "Validation failed", int $code = 422, ?\Throwable $previous = null, array $errors = [])
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Get the validation errors
     * 
     * @return array<string, mixed> The validation errors by field
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the validation errors
     * 
     * @param array<string, mixed> $errors The validation errors to set
     * @return self For method chaining
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }
}