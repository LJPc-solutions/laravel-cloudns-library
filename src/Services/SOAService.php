<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * SOAService
 * 
 * Manages Start of Authority (SOA) record operations including retrieving and modifying SOA settings.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class SOAService
{
    /**
     * Create a new soaservice instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Get SOA details for a zone
     * 
     * @param string $domainName
     * @return array
     */
    public function getDetails(string $domainName): array
    {
        return $this->client->get('dns/get-soa-details', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Modify SOA details
     * 
     * @param string $domainName
     * @param array $updates
     * @return array
     */
    public function modify(string $domainName, array $updates): array
    {
        $params = ['domain-name' => $domainName];

        $mapping = [
            'primary_ns' => 'primary-ns',
            'admin_mail' => 'admin-mail',
            'default_ttl' => 'default-ttl',
            'refresh' => 'refresh',
            'retry' => 'retry',
            'expire' => 'expire',
        ];

        foreach ($mapping as $key => $apiKey) {
            if (isset($updates[$key])) {
                $params[$apiKey] = $updates[$key];
            }
        }

        return $this->client->post('dns/modify-soa-details', $params);
    }

    /**
     * Reset SOA to default values
     * 
     * @param string $domainName
     * @return array
     */
    public function reset(string $domainName): array
    {
        return $this->client->post('dns/reset-soa-details', [
            'domain-name' => $domainName,
        ]);
    }
}