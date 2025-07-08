<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Enums;

/**
 * Monitoring Check Types Enumeration
 * 
 * Represents the different types of monitoring checks available in ClouDNS.
 * Each type monitors different aspects of service availability.
 * 
 * @package LJPc\ClouDNS\Enums
 * @author LJPC
 * @since 1.0.0
 */
enum MonitoringType: string
{
    case DNS = 'dns';
    case TCP = 'tcp';
    case UDP = 'udp';
    case ICMP = 'icmp';
    case SMTP = 'smtp';
    case HTTP = 'http';
    case HTTPS = 'https';
    case SSL = 'ssl';

    /**
     * Check if this monitoring type requires a port number
     * 
     * @return bool True if port is required (TCP, UDP, SMTP, SSL)
     */
    public function requiresPort(): bool
    {
        return match($this) {
            self::TCP, self::UDP, self::SMTP, self::SSL => true,
            default => false,
        };
    }

    /**
     * Check if this monitoring type requires a URL path
     * 
     * @return bool True if path is required (HTTP, HTTPS)
     */
    public function requiresPath(): bool
    {
        return match($this) {
            self::HTTP, self::HTTPS => true,
            default => false,
        };
    }

    /**
     * Get the default port for this monitoring type
     * 
     * @return int|null Default port number, or null if not applicable
     */
    public function getDefaultPort(): ?int
    {
        return match($this) {
            self::SMTP => 25,
            self::HTTP => 80,
            self::HTTPS => 443,
            self::SSL => 443,
            default => null,
        };
    }

    /**
     * Get all monitoring type values as an array
     * 
     * @return array<int, string> Array of all monitoring type string values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}