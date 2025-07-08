<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * AXFRService
 * 
 * Manages AXFR (zone transfer) operations including IP allow list management.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class AXFRService
{
    /**
     * Create a new axfrservice instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Add an IP address to AXFR whitelist
     * 
     * @param string $domainName
     * @param string $ip
     * @return array
     */
    public function addIp(string $domainName, string $ip): array
    {
        return $this->client->post('dns/axfr-add', [
            'domain-name' => $domainName,
            'ip' => $ip,
        ]);
    }

    /**
     * Remove an IP address from AXFR whitelist
     * 
     * @param string $domainName
     * @param int $id
     * @return array
     */
    public function removeIp(string $domainName, int $id): array
    {
        return $this->client->post('dns/axfr-remove', [
            'domain-name' => $domainName,
            'id' => $id,
        ]);
    }

    /**
     * List AXFR whitelist IPs
     * 
     * @param string $domainName
     * @return array
     */
    public function listIps(string $domainName): array
    {
        return $this->client->get('dns/axfr-list', [
            'domain-name' => $domainName,
        ]);
    }
}