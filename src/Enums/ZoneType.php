<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Enums;

/**
 * DNS Zone Types Enumeration
 * 
 * Represents the different types of DNS zones supported by ClouDNS.
 * 
 * @package LJPc\ClouDNS\Enums
 * @author LJPC
 * @since 1.0.0
 */
enum ZoneType: string
{
    case MASTER = 'master';
    case SLAVE = 'slave';
    case PARKED = 'parked';
    case GEODNS = 'geodns';
    case REVERSE = 'reverse';

    /**
     * Get a human-readable description of the zone type
     * 
     * @return string Description of the zone type
     */
    public function getDescription(): string
    {
        return match($this) {
            self::MASTER => 'Master DNS zone',
            self::SLAVE => 'Slave DNS zone',
            self::PARKED => 'Parked domain',
            self::GEODNS => 'GeoDNS zone',
            self::REVERSE => 'Reverse DNS zone',
        };
    }

    /**
     * Check if this zone type requires a master IP address
     * 
     * @return bool True if master IP is required (slave zones only)
     */
    public function requiresMasterIp(): bool
    {
        return $this === self::SLAVE;
    }

    /**
     * Get all zone type values as an array
     * 
     * @return array<int, string> Array of all zone type string values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}