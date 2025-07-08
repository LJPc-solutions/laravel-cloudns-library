<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Exceptions;

/**
 * Resource Not Found Exception
 * 
 * Thrown when a requested resource cannot be found. This occurs when:
 * - A zone doesn't exist
 * - A record ID is invalid
 * - A monitoring check doesn't exist
 * - Any other resource lookup fails
 * 
 * @package LJPc\ClouDNS\Exceptions
 * @author LJPC
 * @since 1.0.0
 */
class ResourceNotFoundException extends ClouDNSException
{
    /**
     * @var string|null The type of resource that was not found
     */
    protected ?string $resourceType = null;

    /**
     * @var string|int|null The identifier of the resource that was not found
     */
    protected string|int|null $resourceId = null;

    /**
     * Create a new resource not found exception
     * 
     * @param string $message The error message
     * @param int $code The HTTP status code (default: 404 Not Found)
     * @param \Throwable|null $previous Previous exception
     * @param array<string, mixed> $context Additional context data
     * @param string|null $resourceType The type of resource (e.g., 'zone', 'record')
     * @param string|int|null $resourceId The resource identifier
     */
    public function __construct(
        string $message = "Resource not found", 
        int $code = 404, 
        ?\Throwable $previous = null, 
        array $context = [],
        ?string $resourceType = null,
        string|int|null $resourceId = null
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
    }

    /**
     * Get the resource type
     * 
     * @return string|null The type of resource that was not found
     */
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * Set the resource type
     * 
     * @param string|null $resourceType The type of resource
     * @return self For method chaining
     */
    public function setResourceType(?string $resourceType): self
    {
        $this->resourceType = $resourceType;
        return $this;
    }

    /**
     * Get the resource identifier
     * 
     * @return string|int|null The resource identifier
     */
    public function getResourceId(): string|int|null
    {
        return $this->resourceId;
    }

    /**
     * Set the resource identifier
     * 
     * @param string|int|null $resourceId The resource identifier
     * @return self For method chaining
     */
    public function setResourceId(string|int|null $resourceId): self
    {
        $this->resourceId = $resourceId;
        return $this;
    }
}