<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * FailoverService
 * 
 * Manages failover operations for DNS records.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class FailoverService
{
    /**
     * Create a new failover service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Activate failover for a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @param array $config
     * @return array
     */
    public function activate(string $domainName, int $recordId, array $config): array
    {
        $params = [
            'domain-name' => $domainName,
            'record-id' => $recordId,
            'check-type' => $config['check_type'],
            'down-event-handler' => $config['down_event_handler'],
            'up-event-handler' => $config['up_event_handler'],
            'main-ip' => $config['main_ip'],
        ];

        if (isset($config['monitoring_region'])) {
            $params['monitoring-region'] = $config['monitoring_region'];
        }

        for ($i = 1; $i <= 4; $i++) {
            if (isset($config["backup_ip_{$i}"])) {
                $params["backup-ip-{$i}"] = $config["backup_ip_{$i}"];
            }
        }

        return $this->client->post('dns/activate-failover', $params);
    }

    /**
     * Deactivate failover for a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return array
     */
    public function deactivate(string $domainName, int $recordId): array
    {
        return $this->client->post('dns/deactivate-failover', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);
    }
}