<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * UtilityService
 * 
 * Provides utility operations including available TTLs, record types, and failover webhooks.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class UtilityService
{
    /**
     * Create a new utility service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Get available TTL values
     * 
     * @return array
     */
    public function getAvailableTTLs(): array
    {
        return $this->client->get('dns/get-available-ttl');
    }

    /**
     * Get available record types for a zone type
     * 
     * @param string $zoneType
     * @return array
     */
    public function getAvailableRecordTypes(string $zoneType): array
    {
        return $this->client->get('dns/get-available-record-types', [
            'zone-type' => $zoneType,
        ]);
    }

    /**
     * Create a webhook for failover notifications
     * 
     * @param string $domainName
     * @param int $recordId
     * @param string $type
     * @param string $webhookUrl
     * @return array
     */
    public function createFailoverWebhook(
        string $domainName,
        int $recordId,
        string $type,
        string $webhookUrl
    ): array {
        return $this->client->post('dns/create-failover-notification', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
            'type' => $type,
            'value' => $webhookUrl,
        ]);
    }
}