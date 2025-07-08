<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\DTOs\Responses;

use LJPc\ClouDNS\Enums\ZoneType;

/**
 * DNS Zone Response DTO
 * 
 * Represents a DNS zone returned from the ClouDNS API.
 * This immutable object contains all zone information.
 * 
 * @package LJPc\ClouDNS\DTOs\Responses
 * @author LJPC
 * @since 1.0.0
 */
readonly class Zone
{
    /**
     * Create a new zone response
     * 
     * @param string $name The zone domain name
     * @param ZoneType $type The type of zone
     * @param string $status Zone status (active/inactive)
     * @param int $recordsCount Number of records in the zone
     * @param string|null $masterIp Master IP for slave zones
     * @param bool|null $isUpdated Whether the zone is updated
     * @param string|null $lastUpdate Last update timestamp
     * @param string|null $created Creation timestamp
     * @param int|null $groupId Zone group ID
     */
    public function __construct(
        public string $name,
        public ZoneType $type,
        public string $status,
        public int $recordsCount,
        public ?string $masterIp = null,
        public ?bool $isUpdated = null,
        public ?string $lastUpdate = null,
        public ?string $created = null,
        public ?int $groupId = null,
    ) {}

    /**
     * Create a zone instance from API response data
     * 
     * @param array<string, mixed> $data The API response data
     * @return self The zone instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            type: ZoneType::from($data['type']),
            status: $data['status'] ?? 'active',
            recordsCount: (int) ($data['records'] ?? 0),
            masterIp: $data['master_ip'] ?? null,
            isUpdated: isset($data['is_updated']) ? (bool) $data['is_updated'] : null,
            lastUpdate: $data['last_update'] ?? null,
            created: $data['created'] ?? null,
            groupId: isset($data['group_id']) ? (int) $data['group_id'] : null,
        );
    }

    /**
     * Convert the zone to an array
     * 
     * Filters out null values for cleaner output.
     * 
     * @return array<string, mixed> The zone data as an array
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'type' => $this->type->value,
            'status' => $this->status,
            'records' => $this->recordsCount,
            'master_ip' => $this->masterIp,
            'is_updated' => $this->isUpdated,
            'last_update' => $this->lastUpdate,
            'created' => $this->created,
            'group_id' => $this->groupId,
        ], fn($value) => $value !== null);
    }
}