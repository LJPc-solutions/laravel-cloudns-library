<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Enums\MonitoringType;
use LJPc\ClouDNS\Enums\RowsPerPage;

/**
 * MonitoringService
 * 
 * Manages monitoring check operations for tracking service availability.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class MonitoringService
{
    /**
     * Create a new monitoring service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Create a monitoring check
     * 
     * @param array $config
     * @return array
     */
    public function create(array $config): array
    {
        $params = [
            'name' => $config['name'],
            'ip' => $config['ip'],
            'monitoring-type' => $config['monitoring_type'],
            'check-period' => $config['check_period'],
        ];

        if (isset($config['port'])) {
            $params['port'] = $config['port'];
        }

        if (isset($config['path'])) {
            $params['path'] = $config['path'];
        }

        if (isset($config['content'])) {
            $params['content'] = $config['content'];
        }

        if (isset($config['timeout'])) {
            $params['timeout'] = $config['timeout'];
        }

        if (isset($config['check_region'])) {
            $params['check-region'] = $config['check_region'];
        }

        return $this->client->post('monitoring/create', $params);
    }

    /**
     * List monitoring checks
     * 
     * @param int $page
     * @param RowsPerPage|int $rowsPerPage
     * @param string|null $search
     * @return array
     */
    public function list(
        int $page = 1,
        RowsPerPage|int $rowsPerPage = RowsPerPage::THIRTY,
        ?string $search = null
    ): array {
        $params = [
            'page' => $page,
            'rows-per-page' => $rowsPerPage instanceof RowsPerPage ? $rowsPerPage->value : $rowsPerPage,
        ];

        if ($search !== null) {
            $params['search'] = $search;
        }

        return $this->client->get('monitoring/list', $params);
    }

    /**
     * Delete a monitoring check
     * 
     * @param int $id
     * @return array
     */
    public function delete(int $id): array
    {
        return $this->client->post('monitoring/delete', [
            'id' => $id,
        ]);
    }
}