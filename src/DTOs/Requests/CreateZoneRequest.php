<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\DTOs\Requests;

use LJPc\ClouDNS\Enums\ZoneType;
use LJPc\ClouDNS\Exceptions\ValidationException;

/**
 * Create DNS Zone Request DTO
 * 
 * Data Transfer Object for creating new DNS zones. Validates zone data
 * and ensures slave zones have a master IP configured.
 * 
 * @package LJPc\ClouDNS\DTOs\Requests
 * @author LJPC
 * @since 1.0.0
 */
readonly class CreateZoneRequest
{
    /**
     * Create a new zone request
     * 
     * @param string $domainName The domain name for the zone
     * @param ZoneType $zoneType The type of zone to create
     * @param string|null $masterIp Master IP address (required for slave zones)
     * @param array<int, string> $nameservers Optional custom nameservers
     * @throws ValidationException If validation fails
     */
    public function __construct(
        public string $domainName,
        public ZoneType $zoneType,
        public ?string $masterIp = null,
        public array $nameservers = [],
    ) {
        $this->validate();
    }

    /**
     * Validate the request data
     * 
     * @return void
     * @throws ValidationException If validation fails
     */
    protected function validate(): void
    {
        if (empty($this->domainName)) {
            throw new ValidationException('Domain name is required');
        }

        if ($this->zoneType->requiresMasterIp() && empty($this->masterIp)) {
            throw new ValidationException("Zone type {$this->zoneType->value} requires master IP");
        }

        if (!empty($this->masterIp) && !filter_var($this->masterIp, FILTER_VALIDATE_IP)) {
            throw new ValidationException('Invalid master IP address');
        }

        foreach ($this->nameservers as $ns) {
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{0,62}(\.[a-zA-Z0-9][a-zA-Z0-9-]{0,62})*$/', $ns)) {
                throw new ValidationException("Invalid nameserver: {$ns}");
            }
        }
    }

    /**
     * Convert the request to an array for the API
     * 
     * @return array<string, mixed> The request data as an array
     */
    public function toArray(): array
    {
        $params = [
            'domain-name' => $this->domainName,
            'zone-type' => $this->zoneType->value,
        ];

        if ($this->masterIp !== null) {
            $params['master-ip'] = $this->masterIp;
        }

        if (!empty($this->nameservers)) {
            $params['ns'] = $this->nameservers;
        }

        return $params;
    }
}