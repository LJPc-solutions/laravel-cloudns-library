<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\DTOs\Requests;

use LJPc\ClouDNS\Enums\RecordType;
use LJPc\ClouDNS\Enums\TTL;
use LJPc\ClouDNS\Exceptions\ValidationException;

/**
 * Create DNS Record Request DTO
 * 
 * Data Transfer Object for creating new DNS records. Validates input data
 * and ensures all required fields are present based on the record type.
 * 
 * @package LJPc\ClouDNS\DTOs\Requests
 * @author LJPC
 * @since 1.0.0
 */
readonly class CreateRecordRequest
{
    /**
     * Create a new record request
     * 
     * @param string $domainName The domain name to add the record to
     * @param RecordType $recordType The type of DNS record
     * @param string $host The hostname (use @ for root domain)
     * @param string $record The record value (IP address, domain, text, etc.)
     * @param int $ttl Time to live in seconds
     * @param int|null $priority Priority for MX and SRV records
     * @param int|null $weight Weight for SRV records
     * @param int|null $port Port for SRV records
     * @param string|null $frame Frame mode for WR records (0=no frame, 1=frame)
     * @param string|null $frameTitle Title for framed WR records
     * @param string|null $frameKeywords Keywords for framed WR records
     * @param string|null $frameDescription Description for framed WR records
     * @param bool|null $savePath Save path for WR records
     * @param int|null $redirectType Redirect type for WR records (301 or 302)
     * @param string|null $geodnsLocation GeoDNS location code
     * @param string|null $caaFlag CAA record flag (0 or 128)
     * @param string|null $caaTag CAA record tag (issue, issuewild, iodef)
     * @param string|null $caaValue CAA record value
     * @param int|null $sshfpAlgorithm SSHFP algorithm (1=RSA, 2=DSA, 3=ECDSA, 4=Ed25519)
     * @param int|null $sshfpFpType SSHFP fingerprint type (1=SHA-1, 2=SHA-256)
     * @param string|null $sshfpFingerprint SSHFP fingerprint in hex
     * @param int|null $tlsaUsage TLSA usage (0-3)
     * @param int|null $tlsaSelector TLSA selector (0=cert, 1=pubkey)
     * @param int|null $tlsaMatchingType TLSA matching type (0=full, 1=SHA-256, 2=SHA-512)
     * @param string|null $tlsaCertificate TLSA certificate data in hex
     * @throws ValidationException If validation fails
     */
    public function __construct(
        public string $domainName,
        public RecordType $recordType,
        public string $host,
        public string $record,
        public int $ttl,
        public ?int $priority = null,
        public ?int $weight = null,
        public ?int $port = null,
        public ?string $frame = null,
        public ?string $frameTitle = null,
        public ?string $frameKeywords = null,
        public ?string $frameDescription = null,
        public ?bool $savePath = null,
        public ?int $redirectType = null,
        public ?string $geodnsLocation = null,
        public ?string $caaFlag = null,
        public ?string $caaTag = null,
        public ?string $caaValue = null,
        public ?int $sshfpAlgorithm = null,
        public ?int $sshfpFpType = null,
        public ?string $sshfpFingerprint = null,
        public ?int $tlsaUsage = null,
        public ?int $tlsaSelector = null,
        public ?int $tlsaMatchingType = null,
        public ?string $tlsaCertificate = null,
    ) {
        $this->validate();
    }

    /**
     * Validate the request data
     * 
     * @return void
     * @throws ValidationException If validation fails
     */
    protected function validate(): void
    {
        if (!TTL::isValid($this->ttl)) {
            throw new ValidationException('Invalid TTL value. Allowed values: ' . implode(', ', TTL::values()));
        }

        if ($this->recordType->requiresPriority() && $this->priority === null) {
            throw new ValidationException("Record type {$this->recordType->value} requires priority");
        }

        if ($this->recordType->requiresPort() && $this->port === null) {
            throw new ValidationException("Record type {$this->recordType->value} requires port");
        }

        if ($this->recordType->requiresWeight() && $this->weight === null) {
            throw new ValidationException("Record type {$this->recordType->value} requires weight");
        }

        if ($this->recordType === RecordType::CAA) {
            if ($this->caaFlag === null || $this->caaTag === null || $this->caaValue === null) {
                throw new ValidationException('CAA record requires flag, tag, and value');
            }
        }

        if ($this->recordType === RecordType::SSHFP) {
            if ($this->sshfpAlgorithm === null || $this->sshfpFpType === null || $this->sshfpFingerprint === null) {
                throw new ValidationException('SSHFP record requires algorithm, fingerprint type, and fingerprint');
            }
        }

        if ($this->recordType === RecordType::TLSA) {
            if ($this->tlsaUsage === null || $this->tlsaSelector === null || 
                $this->tlsaMatchingType === null || $this->tlsaCertificate === null) {
                throw new ValidationException('TLSA record requires usage, selector, matching type, and certificate');
            }
        }
    }

    /**
     * Convert the request to an array for the API
     * 
     * @return array<string, mixed> The request data as an array
     */
    public function toArray(): array
    {
        $params = [
            'domain-name' => $this->domainName,
            'record-type' => $this->recordType->value,
            'host' => $this->host,
            'record' => $this->buildRecordValue(),
            'ttl' => $this->ttl,
        ];

        if ($this->priority !== null) {
            $params['priority'] = $this->priority;
        }

        if ($this->weight !== null) {
            $params['weight'] = $this->weight;
        }

        if ($this->port !== null) {
            $params['port'] = $this->port;
        }

        if ($this->recordType === RecordType::WR) {
            if ($this->frame !== null) $params['frame'] = $this->frame;
            if ($this->frameTitle !== null) $params['frame-title'] = $this->frameTitle;
            if ($this->frameKeywords !== null) $params['frame-keywords'] = $this->frameKeywords;
            if ($this->frameDescription !== null) $params['frame-description'] = $this->frameDescription;
            if ($this->savePath !== null) $params['save-path'] = $this->savePath ? '1' : '0';
            if ($this->redirectType !== null) $params['redirect-type'] = $this->redirectType;
        }

        if ($this->geodnsLocation !== null) {
            $params['geodns-location'] = $this->geodnsLocation;
        }

        return $params;
    }

    /**
     * Build the record value based on record type
     * 
     * Special record types like CAA, SSHFP, and TLSA require
     * their values to be formatted in a specific way.
     * 
     * @return string The formatted record value
     */
    protected function buildRecordValue(): string
    {
        return match($this->recordType) {
            RecordType::CAA => "{$this->caaFlag} {$this->caaTag} {$this->caaValue}",
            RecordType::SSHFP => "{$this->sshfpAlgorithm} {$this->sshfpFpType} {$this->sshfpFingerprint}",
            RecordType::TLSA => "{$this->tlsaUsage} {$this->tlsaSelector} {$this->tlsaMatchingType} {$this->tlsaCertificate}",
            default => $this->record,
        };
    }
}