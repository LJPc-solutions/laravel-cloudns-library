<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Services;

use LJPc\ClouDNS\Client\ClouDNSClient;

/**
 * DNSSECService
 * 
 * Manages DNSSEC (Domain Name System Security Extensions) operations including
 * DS records, DNSKEY records, and DNSSEC status management.
 * 
 * @package LJPc\ClouDNS\Services
 * @author LJPC
 * @since 1.0.0
 */
class DNSSECService
{
    /**
     * Create a new DNSSEC service instance
     * 
     * @param ClouDNSClient $client The ClouDNS HTTP client
     */
    public function __construct(
        protected ClouDNSClient $client
    ) {}

    /**
     * Activate DNSSEC for a domain
     * 
     * @param string $domainName The domain name to activate DNSSEC for
     * @return array{status: string, statusDescription: string}
     */
    public function activate(string $domainName): array
    {
        return $this->client->post('dns/activate-dnssec', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Deactivate DNSSEC for a domain
     * 
     * @param string $domainName The domain name to deactivate DNSSEC for
     * @return array{status: string, statusDescription: string}
     */
    public function deactivate(string $domainName): array
    {
        return $this->client->post('dns/deactivate-dnssec', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Get DS records for a domain
     * 
     * Returns the Delegation Signer (DS) records that should be added to the parent zone.
     * 
     * @param string $domainName The domain name to get DS records for
     * @return array{status: string, ds: array<array{digest_type: int, algorithm: int, digest: string, key_tag: int}>}
     */
    public function getDsRecords(string $domainName): array
    {
        return $this->client->get('dns/get-dnssec-ds-records', [
            'domain-name' => $domainName,
        ]);
    }

    /**
     * Get available DNSSEC algorithms
     * 
     * Common algorithms:
     * - 5: RSA/SHA-1
     * - 7: RSASHA1-NSEC3-SHA1
     * - 8: RSA/SHA-256
     * - 10: RSA/SHA-512
     * - 13: ECDSA Curve P-256 with SHA-256
     * - 14: ECDSA Curve P-384 with SHA-384
     * 
     * @return array<int, string> Array of algorithm ID => algorithm name
     */
    public function getAvailableAlgorithms(): array
    {
        return [
            5 => 'RSA/SHA-1',
            7 => 'RSASHA1-NSEC3-SHA1',
            8 => 'RSA/SHA-256',
            10 => 'RSA/SHA-512',
            13 => 'ECDSA Curve P-256 with SHA-256',
            14 => 'ECDSA Curve P-384 with SHA-384',
        ];
    }

    /**
     * Add DS records from the parent zone
     * 
     * This is used for zones where the parent zone has DS records that need to be imported.
     * 
     * @param string $domainName The domain name
     * @param int $keyTag The key tag from the DS record
     * @param int $algorithm The algorithm number (e.g., 7 for RSASHA1-NSEC3-SHA1, 8 for RSASHA256)
     * @param int $digestType The digest type (1 for SHA1, 2 for SHA256)
     * @param string $digest The digest value (hexadecimal)
     * @return array{status: string, statusDescription: string}
     */
    public function addDsRecord(
        string $domainName,
        int $keyTag,
        int $algorithm,
        int $digestType,
        string $digest
    ): array {
        return $this->client->post('dns/add-dnssec-ds-record', [
            'domain-name' => $domainName,
            'key-tag' => $keyTag,
            'algorithm' => $algorithm,
            'digest-type' => $digestType,
            'digest' => $digest,
        ]);
    }

    /**
     * Remove DS records
     * 
     * Removes DS records that were previously added from the parent zone.
     * 
     * @param string $domainName The domain name
     * @param int|null $keyTag Optional key tag to remove specific record
     * @return array{status: string, statusDescription: string}
     */
    public function removeDsRecord(string $domainName, ?int $keyTag = null): array
    {
        $params = ['domain-name' => $domainName];
        
        if ($keyTag !== null) {
            $params['key-tag'] = $keyTag;
        }
        
        return $this->client->post('dns/remove-dnssec-ds-record', $params);
    }

    /**
     * Check if DNSSEC is available for a domain
     * 
     * @param string $domainName The domain name to check
     * @return bool True if DNSSEC is available, false otherwise
     */
    public function isAvailable(string $domainName): bool
    {
        $response = $this->client->get('dns/is-dnssec-available', [
            'domain-name' => $domainName,
        ]);
        
        return isset($response['available']) && $response['available'] === '1';
    }

    /**
     * Set DNSSEC OPTOUT status
     * 
     * @param string $domainName The domain name
     * @param bool $enabled True to enable OPTOUT, false to disable
     * @return array{status: string, statusDescription: string}
     */
    public function setOptOut(string $domainName, bool $enabled): array
    {
        return $this->client->post('dns/set-dnssec-optout', [
            'domain-name' => $domainName,
            'status' => $enabled ? '1' : '0',
        ]);
    }

    /**
     * Check if DNSSEC is active for a domain
     * 
     * @param string $domainName The domain name to check
     * @return bool True if DNSSEC is active, false otherwise
     */
    public function isActive(string $domainName): bool
    {
        try {
            // Try to get DS records - if successful, DNSSEC is active
            $response = $this->getDsRecords($domainName);
            return isset($response['status']) && $response['status'] === 'Success';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get DNSSEC status information
     * 
     * Combines availability check and DS records retrieval to provide comprehensive status.
     * 
     * @param string $domainName The domain name to check
     * @return array{available: bool, active: bool, ds_records?: array}
     */
    public function getStatus(string $domainName): array
    {
        $status = [
            'available' => $this->isAvailable($domainName),
            'active' => false,
            'ds_records' => [],
        ];
        
        if ($status['available']) {
            try {
                $dsRecords = $this->getDsRecords($domainName);
                if (isset($dsRecords['status']) && $dsRecords['status'] === 'Success') {
                    $status['active'] = true;
                    $status['ds_records'] = $dsRecords['ds'] ?? [];
                }
            } catch (\Exception $e) {
                // DNSSEC not active
            }
        }
        
        return $status;
    }
}