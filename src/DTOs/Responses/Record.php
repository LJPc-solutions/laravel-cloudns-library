<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\DTOs\Responses;

use LJPc\ClouDNS\Enums\RecordType;

/**
 * DNS Record Response DTO
 * 
 * Represents a DNS record returned from the ClouDNS API.
 * This immutable object contains all record information including
 * type-specific fields for advanced record types.
 * 
 * @package LJPc\ClouDNS\DTOs\Responses
 * @author LJPC
 * @since 1.0.0
 */
readonly class Record
{
    /**
     * Create a new record response
     * 
     * @param int $id The record ID
     * @param RecordType $type The record type
     * @param string $host The hostname
     * @param string $record The record value
     * @param int $ttl Time to live in seconds
     * @param int|null $priority Priority for MX/SRV records
     * @param int|null $weight Weight for SRV records
     * @param int|null $port Port for SRV records
     * @param string|null $frame Frame mode for WR records
     * @param string|null $frameTitle Title for framed WR records
     * @param string|null $frameKeywords Keywords for framed WR records
     * @param string|null $frameDescription Description for framed WR records
     * @param bool|null $savePath Save path setting for WR records
     * @param int|null $redirectType Redirect type for WR records
     * @param string|null $geodnsLocation GeoDNS location
     * @param bool|null $isActive Whether the record is active
     * @param string|null $created Creation timestamp
     * @param string|null $modified Last modification timestamp
     */
    public function __construct(
        public int $id,
        public RecordType $type,
        public string $host,
        public string $record,
        public int $ttl,
        public ?int $priority = null,
        public ?int $weight = null,
        public ?int $port = null,
        public ?string $frame = null,
        public ?string $frameTitle = null,
        public ?string $frameKeywords = null,
        public ?string $frameDescription = null,
        public ?bool $savePath = null,
        public ?int $redirectType = null,
        public ?string $geodnsLocation = null,
        public ?bool $isActive = null,
        public ?string $created = null,
        public ?string $modified = null,
    ) {}

    /**
     * Create a record instance from API response data
     * 
     * @param array<string, mixed> $data The API response data
     * @return self The record instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            type: RecordType::from($data['type']),
            host: $data['host'],
            record: $data['record'],
            ttl: (int) $data['ttl'],
            priority: isset($data['priority']) ? (int) $data['priority'] : null,
            weight: isset($data['weight']) ? (int) $data['weight'] : null,
            port: isset($data['port']) ? (int) $data['port'] : null,
            frame: $data['frame'] ?? null,
            frameTitle: $data['frame_title'] ?? null,
            frameKeywords: $data['frame_keywords'] ?? null,
            frameDescription: $data['frame_description'] ?? null,
            savePath: isset($data['save_path']) ? (bool) $data['save_path'] : null,
            redirectType: isset($data['redirect_type']) ? (int) $data['redirect_type'] : null,
            geodnsLocation: $data['geodns_location'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
            created: $data['created'] ?? null,
            modified: $data['modified'] ?? null,
        );
    }

    /**
     * Convert the record to an array
     * 
     * Filters out null values for cleaner output.
     * 
     * @return array<string, mixed> The record data as an array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type->value,
            'host' => $this->host,
            'record' => $this->record,
            'ttl' => $this->ttl,
            'priority' => $this->priority,
            'weight' => $this->weight,
            'port' => $this->port,
            'frame' => $this->frame,
            'frame_title' => $this->frameTitle,
            'frame_keywords' => $this->frameKeywords,
            'frame_description' => $this->frameDescription,
            'save_path' => $this->savePath,
            'redirect_type' => $this->redirectType,
            'geodns_location' => $this->geodnsLocation,
            'is_active' => $this->isActive,
            'created' => $this->created,
            'modified' => $this->modified,
        ], fn($value) => $value !== null);
    }
}