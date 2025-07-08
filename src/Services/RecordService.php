<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;
use LJPc\ClouDNS\DTOs\Requests\CreateRecordRequest;
use LJPc\ClouDNS\DTOs\Responses\Record;
use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Enums\RowsPerPage;
use LJPc\ClouDNS\Enums\TTL;

/**
 * RecordService
 * 
 * Manages DNS record operations including CRUD operations, bulk operations, and import/export functionality.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class RecordService
{
    /**
     * Create a new record service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * List DNS records
     * 
     * @param string $domainName
     * @param string|null $host
     * @param RecordType|string|null $type
     * @param int $page
     * @param RowsPerPage|int $rowsPerPage
     * @return array{records: Record[], page: int, pages: int}
     */
    public function list(
        string $domainName,
        ?string $host = null,
        RecordType|string|null $type = null,
        int $page = 1,
        RowsPerPage|int $rowsPerPage = RowsPerPage::THIRTY
    ): array {
        $params = [
            'domain-name' => $domainName,
            'page' => $page,
            'rows-per-page' => $rowsPerPage instanceof RowsPerPage ? $rowsPerPage->value : $rowsPerPage,
        ];

        if ($host !== null) {
            $params['host'] = $host;
        }

        if ($type !== null) {
            $params['type'] = $type instanceof RecordType ? $type->value : $type;
        }

        $response = $this->client->get('dns/records', $params);
        
        $records = [];
        foreach ($response as $key => $recordData) {
            if (is_numeric($key) && is_array($recordData)) {
                $records[] = Record::fromArray($recordData);
            }
        }

        return [
            'records' => $records,
            'page' => $response['page'] ?? $page,
            'pages' => $response['pages'] ?? 1,
        ];
    }

    /**
     * Get a single record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return Record
     */
    public function get(string $domainName, int $recordId): Record
    {
        $response = $this->client->get('dns/get-record', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);

        return Record::fromArray($response);
    }

    /**
     * Add a new record
     * 
     * @param CreateRecordRequest|array $request
     * @return int The ID of the created record
     */
    public function create(CreateRecordRequest|array $request): int
    {
        if (is_array($request)) {
            $request = new CreateRecordRequest(
                domainName: $request['domain_name'],
                recordType: RecordType::from($request['record_type']),
                host: $request['host'],
                record: $request['record'],
                ttl: $request['ttl'] ?? TTL::getDefault()->value,
                priority: $request['priority'] ?? null,
                weight: $request['weight'] ?? null,
                port: $request['port'] ?? null,
                frame: $request['frame'] ?? null,
                frameTitle: $request['frame_title'] ?? null,
                frameKeywords: $request['frame_keywords'] ?? null,
                frameDescription: $request['frame_description'] ?? null,
                savePath: $request['save_path'] ?? null,
                redirectType: $request['redirect_type'] ?? null,
                geodnsLocation: $request['geodns_location'] ?? null,
                caaFlag: $request['caa_flag'] ?? null,
                caaTag: $request['caa_tag'] ?? null,
                caaValue: $request['caa_value'] ?? null,
                sshfpAlgorithm: $request['sshfp_algorithm'] ?? null,
                sshfpFpType: $request['sshfp_fp_type'] ?? null,
                sshfpFingerprint: $request['sshfp_fingerprint'] ?? null,
                tlsaUsage: $request['tlsa_usage'] ?? null,
                tlsaSelector: $request['tlsa_selector'] ?? null,
                tlsaMatchingType: $request['tlsa_matching_type'] ?? null,
                tlsaCertificate: $request['tlsa_certificate'] ?? null,
            );
        }

        $response = $this->client->post('dns/add-record', $request->toArray());
        
        // Extract record ID from response message
        if (preg_match('/\[(\d+)\]/', $response['statusDescription'] ?? '', $matches)) {
            return (int) $matches[1];
        }

        throw new \RuntimeException('Failed to extract record ID from response');
    }

    /**
     * Update a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @param array $updates
     * @return array
     */
    public function update(string $domainName, int $recordId, array $updates): array
    {
        $params = [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ];

        // Map array keys to API parameter names
        $mapping = [
            'host' => 'host',
            'record' => 'record',
            'ttl' => 'ttl',
            'priority' => 'priority',
            'weight' => 'weight',
            'port' => 'port',
            'frame' => 'frame',
            'frame_title' => 'frame-title',
            'frame_keywords' => 'frame-keywords',
            'frame_description' => 'frame-description',
            'save_path' => 'save-path',
            'redirect_type' => 'redirect-type',
            'geodns_location' => 'geodns-location',
        ];

        foreach ($mapping as $key => $apiKey) {
            if (isset($updates[$key])) {
                $params[$apiKey] = $updates[$key];
            }
        }

        return $this->client->post('dns/mod-record', $params);
    }

    /**
     * Delete a record
     * 
     * @param string $domainName
     * @param int $recordId
     * @return array
     */
    public function delete(string $domainName, int $recordId): array
    {
        return $this->client->post('dns/delete-record', [
            'domain-name' => $domainName,
            'record-id' => $recordId,
        ]);
    }

    /**
     * Copy records from one domain to another
     * 
     * @param string $fromDomain
     * @param string $toDomain
     * @param bool $deleteCurrentRecords
     * @return array
     */
    public function copy(string $fromDomain, string $toDomain, bool $deleteCurrentRecords = false): array
    {
        return $this->client->post('dns/copy-records', [
            'from-domain' => $fromDomain,
            'to-domain' => $toDomain,
            'delete-current-records' => $deleteCurrentRecords ? '1' : '0',
        ]);
    }

    /**
     * Import records
     * 
     * @param string $domainName
     * @param string $content
     * @param string $format
     * @param bool $deleteExistingRecords
     * @param array $recordTypes
     * @return array
     */
    public function import(
        string $domainName,
        string $content,
        string $format = 'bind',
        bool $deleteExistingRecords = false,
        array $recordTypes = []
    ): array {
        $params = [
            'domain-name' => $domainName,
            'format' => $format,
            'content' => $content,
            'delete-existing-records' => $deleteExistingRecords ? '1' : '0',
        ];

        if (!empty($recordTypes)) {
            $params['record-types'] = $recordTypes;
        }

        return $this->client->post('dns/records-import', $params);
    }

    /**
     * Export records
     * 
     * @param string $domainName
     * @return string
     */
    public function export(string $domainName): string
    {
        $response = $this->client->get('dns/records-export', [
            'domain-name' => $domainName,
        ]);

        return $response['zone'] ?? '';
    }

    /**
     * Get all records (handles pagination automatically)
     * 
     * @param string $domainName
     * @param string|null $host
     * @param RecordType|string|null $type
     * @return Record[]
     */
    public function getAll(
        string $domainName,
        ?string $host = null,
        RecordType|string|null $type = null
    ): array {
        $allRecords = [];
        $page = 1;
        $pages = 1;

        do {
            $result = $this->list($domainName, $host, $type, $page, RowsPerPage::HUNDRED);
            $allRecords = array_merge($allRecords, $result['records']);
            $pages = $result['pages'];
            $page++;
        } while ($page <= $pages);

        return $allRecords;
    }

    /**
     * Delete multiple records
     * 
     * @param string $domainName
     * @param array $recordIds
     * @return array Results indexed by record ID
     */
    public function deleteMultiple(string $domainName, array $recordIds): array
    {
        $results = [];

        foreach ($recordIds as $recordId) {
            try {
                $results[$recordId] = $this->delete($domainName, $recordId);
            } catch (\Exception $e) {
                $results[$recordId] = [
                    'status' => 'Failed',
                    'statusDescription' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}