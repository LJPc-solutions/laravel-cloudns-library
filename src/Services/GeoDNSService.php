<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * GeoDNSService
 * 
 * Manages GeoDNS operations for location-based DNS responses.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class GeoDNSService
{
    /**
     * Create a new geo dnsservice instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Get available GeoDNS locations
     * 
     * @return array
     */
    public function getLocations(): array
    {
        return $this->client->get('dns/geodns-locations');
    }

    /**
     * Check if GeoDNS is available for a domain
     * 
     * @param string $domainName
     * @return bool
     */
    public function isAvailable(string $domainName): bool
    {
        $response = $this->client->get('dns/is-geodns-available', [
            'domain-name' => $domainName,
        ]);

        return $response['available'] ?? false;
    }
}