<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * SlaveZoneService
 * 
 * Manages slave zone operations including master server configuration.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class SlaveZoneService
{
    /**
     * Create a new slave zone service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Add a master server to a slave zone
     * 
     * @param string $domainName
     * @param string $masterIp
     * @return array
     */
    public function addMasterServer(string $domainName, string $masterIp): array
    {
        return $this->client->post('dns/add-master-server', [
            'domain-name' => $domainName,
            'master-ip' => $masterIp,
        ]);
    }

    /**
     * Delete a master server from a slave zone
     * 
     * @param string $domainName
     * @param int $id
     * @return array
     */
    public function deleteMasterServer(string $domainName, int $id): array
    {
        return $this->client->post('dns/delete-master-server', [
            'domain-name' => $domainName,
            'id' => $id,
        ]);
    }

    /**
     * List master servers for a slave zone
     * 
     * @param string $domainName
     * @return array
     */
    public function listMasterServers(string $domainName): array
    {
        return $this->client->get('dns/list-master-servers', [
            'domain-name' => $domainName,
        ]);
    }
}