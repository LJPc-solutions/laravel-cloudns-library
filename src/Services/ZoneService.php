<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\DTOs\Requests\CreateZoneRequest;
use LJPc\ClouDNS\DTOs\Responses\Zone;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Enums\ZoneType;

/**
 * Zone Service
 * 
 * Manages DNS zone operations including creating, deleting, listing zones,
 * and retrieving zone information and statistics.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class ZoneService
{
    /**
     * Create a new zone service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * List DNS zones
     * 
     * @param int $page
     * @param RowsPerPage|int $rowsPerPage
     * @param string|null $search
     * @param int|null $groupId
     * @return array{page: int, pages: int, zones: Zone[]}
     */
    public function list(
        int $page = 1, 
        RowsPerPage|int $rowsPerPage = RowsPerPage::THIRTY,
        ?string $search = null,
        ?int $groupId = null
    ): array {
        $params = [
            'page' => $page,
            'rows-per-page' => $rowsPerPage instanceof RowsPerPage ? $rowsPerPage->value : $rowsPerPage,
        ];

        if ($search !== null) {
            $params['search'] = $search;
        }

        if ($groupId !== null) {
            $params['group-id'] = $groupId;
        }

        $response = $this->client->get('dns/list-zones', $params);

        $zones = [];
        foreach ($response as $key => $zoneData) {
            if (is_numeric($key) && is_array($zoneData)) {
                $zones[] = Zone::fromArray($zoneData);
            }
        }

        return [
            'page' => $response['page'] ?? $page,
            'pages' => $response['pages'] ?? 1,
            'zones' => $zones,
        ];
    }

    /**
     * Register a new DNS zone
     * 
     * @param CreateZoneRequest|array $request
     * @return array
     */
    public function create(CreateZoneRequest|array $request): array
    {
        if (is_array($request)) {
            $request = new CreateZoneRequest(
                domainName: $request['domain_name'],
                zoneType: ZoneType::from($request['zone_type']),
                masterIp: $request['master_ip'] ?? null,
                nameservers: $request['nameservers'] ?? []
            );
        }

        return $this->client->post('dns/register', $request->toArray());
    }

    /**
     * Delete a DNS zone
     * 
     * @param string $domainName
     * @return array
     */
    public function delete(string $domainName): array
    {
        return $this->client->post('dns/delete', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Get zone information
     * 
     * @param string $domainName
     * @return array
     */
    public function getInfo(string $domainName): array
    {
        return $this->client->get('dns/get-zone-info', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Get zones statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return $this->client->get('dns/get-zones-stats');
    }

    /**
     * Update zone status
     * 
     * @param string $domainName
     * @param string $status
     * @return array
     */
    public function updateStatus(string $domainName, string $status): array
    {
        return $this->client->post('dns/change-status', [
            'domain-name' => $domainName,
            'status' => $status,
        ]);
    }

    /**
     * Get page count for listing zones
     * 
     * @param string|null $search
     * @param int|null $groupId
     * @return int
     */
    public function getPageCount(?string $search = null, ?int $groupId = null): int
    {
        $params = [];

        if ($search !== null) {
            $params['search'] = $search;
        }

        if ($groupId !== null) {
            $params['group-id'] = $groupId;
        }

        $response = $this->client->get('dns/get-pages-count', $params);
        return (int) ($response['pages'] ?? 1);
    }

    /**
     * Check if zone exists
     * 
     * @param string $domainName
     * @return bool
     */
    public function exists(string $domainName): bool
    {
        try {
            $this->getInfo($domainName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all zones (handles pagination automatically)
     * 
     * @param string|null $search
     * @param int|null $groupId
     * @return Zone[]
     */
    public function getAll(?string $search = null, ?int $groupId = null): array
    {
        $allZones = [];
        $page = 1;
        $pages = 1;

        do {
            $result = $this->list($page, RowsPerPage::HUNDRED, $search, $groupId);
            $allZones = array_merge($allZones, $result['zones']);
            $pages = $result['pages'];
            $page++;
        } while ($page <= $pages);

        return $allZones;
    }
}