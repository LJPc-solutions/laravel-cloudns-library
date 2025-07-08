<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\Enums\RowsPerPage;

/**
 * DynamicDNSService
 * 
 * Manages Dynamic DNS operations allowing IP addresses to be updated dynamically for DNS records.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class DynamicDNSService
{
    /**
     * Create a new dynamic dnsservice instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Get dynamic URL for a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return string
     */
    public function getDynamicUrl(string $domainName, int $recordId): string
    {
        $response = $this->client->get('dns/get-dynamic-url', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);

        return $response['url'] ?? '';
    }

    /**
     * Disable dynamic URL for a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return array
     */
    public function disableDynamicUrl(string $domainName, int $recordId): array
    {
        return $this->client->post('dns/disable-dynamic-url', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);
    }

    /**
     * Change (regenerate) dynamic URL for a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return string The new dynamic URL
     */
    public function changeDynamicUrl(string $domainName, int $recordId): string
    {
        $response = $this->client->post('dns/change-dynamic-url', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);

        return $response['url'] ?? '';
    }

    /**
     * Get dynamic URL update history
     * 
     * @param string $domainName
     * @param int $recordId
     * @param int $page
     * @param RowsPerPage|int $rowsPerPage
     * @return array{history: array[], page: int, pages: int}
     */
    public function getHistory(
        string $domainName,
        int $recordId,
        int $page = 1,
        RowsPerPage|int $rowsPerPage = RowsPerPage::THIRTY
    ): array {
        $response = $this->client->get('dns/get-dynamic-url-history', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
            'page' => $page,
            'rows-per-page' => $rowsPerPage instanceof RowsPerPage ? $rowsPerPage->value : $rowsPerPage,
        ]);

        $history = [];
        foreach ($response as $key => $entry) {
            if (is_numeric($key) && is_array($entry)) {
                $history[] = [
                    'date' => $entry['date'] ?? '',
                    'ip' => $entry['ip'] ?? '',
                    'user_agent' => $entry['user_agent'] ?? '',
                ];
            }
        }

        return [
            'history' => $history,
            'page' => $response['page'] ?? $page,
            'pages' => $response['pages'] ?? 1,
        ];
    }

    /**
     * Update IP address using dynamic URL
     * 
     * @param string $dynamicUrl
     * @param string|null $ip Optional IP address (if not provided, uses client's IP)
     * @return array
     */
    public function updateIp(string $dynamicUrl, ?string $ip = null): array
    {
        // Extract the query parameter from the dynamic URL
        $urlParts = parse_url($dynamicUrl);
        parse_str($urlParts['query'] ?? '', $queryParams);
        
        if (!isset($queryParams['q'])) {
            throw new \InvalidArgumentException('Invalid dynamic URL format');
        }

        $params = ['q' => $queryParams['q']];
        
        if ($ip !== null) {
            $params['ip'] = $ip;
        }

        // Dynamic DNS updates use a different endpoint
        $client = new \GuzzleHttp\Client();
        $response = $client->get($dynamicUrl, [
            'query' => $params,
            'http_errors' => false,
        ]);

        $body = (string) $response->getBody();

        return [
            'status' => $response->getStatusCode() === 200 ? 'Success' : 'Failed',
            'response' => $body,
            'ip' => $ip ?? 'auto-detected',
        ];
    }

    /**
     * Check if a record has dynamic DNS enabled
     * 
     * @param string $domainName
     * @param int $recordId
     * @return bool
     */
    public function isEnabled(string $domainName, int $recordId): bool
    {
        try {
            $url = $this->getDynamicUrl($domainName, $recordId);
            return !empty($url);
        } catch (\Exception $e) {
            return false;
        }
    }
}