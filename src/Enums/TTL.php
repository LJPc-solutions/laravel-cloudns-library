<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Enums;

/**
 * DNS Time To Live (TTL) Values Enumeration
 * 
 * Provides common TTL values for DNS records in seconds.
 * TTL determines how long DNS resolvers should cache records.
 * 
 * @package LJPc\ClouDNS\Enums
 * @author LJPC
 * @since 1.0.0
 */
enum TTL: int
{
    case MINUTE_1 = 60;
    case MINUTES_5 = 300;
    case MINUTES_15 = 900;
    case MINUTES_30 = 1800;
    case HOUR_1 = 3600;
    case HOURS_6 = 21600;
    case HOURS_12 = 43200;
    case DAY_1 = 86400;
    case DAYS_2 = 172800;
    case DAYS_3 = 259200;
    case WEEK_1 = 604800;
    case WEEKS_2 = 1209600;
    case MONTH_1 = 2592000;

    /**
     * Get a human-readable label for the TTL value
     * 
     * @return string Human-readable time duration
     */
    public function getLabel(): string
    {
        return match($this) {
            self::MINUTE_1 => '1 minute',
            self::MINUTES_5 => '5 minutes',
            self::MINUTES_15 => '15 minutes',
            self::MINUTES_30 => '30 minutes',
            self::HOUR_1 => '1 hour',
            self::HOURS_6 => '6 hours',
            self::HOURS_12 => '12 hours',
            self::DAY_1 => '1 day',
            self::DAYS_2 => '2 days',
            self::DAYS_3 => '3 days',
            self::WEEK_1 => '1 week',
            self::WEEKS_2 => '2 weeks',
            self::MONTH_1 => '1 month',
        };
    }

    /**
     * Get all TTL values as an array
     * 
     * @return array<int, int> Array of all TTL values in seconds
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a TTL value is valid
     * 
     * @param int $ttl The TTL value to check in seconds
     * @return bool True if the TTL value is valid
     */
    public static function isValid(int $ttl): bool
    {
        return in_array($ttl, self::values(), true);
    }

    /**
     * Get the default TTL value
     * 
     * @return self The default TTL (1 hour)
     */
    public static function getDefault(): self
    {
        return self::HOUR_1;
    }
}