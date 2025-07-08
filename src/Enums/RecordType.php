<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Enums;

/**
 * DNS Record Types Enumeration
 * 
 * This enum represents all DNS record types supported by ClouDNS,
 * including standard DNS types and ClouDNS-specific extensions.
 * 
 * @package LJPc\ClouDNS\Enums
 * @author LJPC
 * @since 1.0.0
 */
enum RecordType: string
{
    // Standard Types
    case A = 'A';
    case AAAA = 'AAAA';
    case CNAME = 'CNAME';
    case MX = 'MX';
    case TXT = 'TXT';
    case NS = 'NS';
    case PTR = 'PTR';
    case SRV = 'SRV';
    case SPF = 'SPF';
    
    // Advanced Types
    case CAA = 'CAA';
    case SSHFP = 'SSHFP';
    case TLSA = 'TLSA';
    case NAPTR = 'NAPTR';
    case RP = 'RP';
    case CERT = 'CERT';
    case OPENPGPKEY = 'OPENPGPKEY';
    case HINFO = 'HINFO';
    case SMIMEA = 'SMIMEA';
    case DNAME = 'DNAME';
    case LOC = 'LOC';
    
    // ClouDNS-Specific Types
    case WR = 'WR';
    case ALIAS = 'ALIAS';

    /**
     * Check if this record type requires a priority value
     * 
     * @return bool True if priority is required (MX, SRV records)
     */
    public function requiresPriority(): bool
    {
        return match($this) {
            self::MX, self::SRV => true,
            default => false,
        };
    }

    /**
     * Check if this record type requires a port value
     * 
     * @return bool True if port is required (SRV records only)
     */
    public function requiresPort(): bool
    {
        return $this === self::SRV;
    }

    /**
     * Check if this record type requires a weight value
     * 
     * @return bool True if weight is required (SRV records only)
     */
    public function requiresWeight(): bool
    {
        return $this === self::SRV;
    }

    /**
     * Get a human-readable description of the record type
     * 
     * @return string Description of what this record type is used for
     */
    public function getDescription(): string
    {
        return match($this) {
            self::A => 'IPv4 address',
            self::AAAA => 'IPv6 address',
            self::CNAME => 'Canonical name',
            self::MX => 'Mail exchange',
            self::TXT => 'Text record',
            self::NS => 'Name server',
            self::PTR => 'Pointer record',
            self::SRV => 'Service record',
            self::SPF => 'Sender Policy Framework',
            self::CAA => 'Certificate Authority Authorization',
            self::SSHFP => 'SSH fingerprint',
            self::TLSA => 'TLS authentication',
            self::NAPTR => 'Naming Authority Pointer',
            self::RP => 'Responsible Person',
            self::CERT => 'Certificate record',
            self::OPENPGPKEY => 'OpenPGP key',
            self::HINFO => 'Host information',
            self::SMIMEA => 'S/MIME association',
            self::DNAME => 'Delegation name',
            self::LOC => 'Location information',
            self::WR => 'Web Redirect',
            self::ALIAS => 'Similar to CNAME but for root domain',
        };
    }

    /**
     * Get all record type values as an array
     * 
     * @return array<int, string> Array of all record type string values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}