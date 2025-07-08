<?php

declare(strict_types=1);

namespace LJPc\ClouDNS\Enums;

/**
 * Pagination Rows Per Page Enumeration
 * 
 * Defines the allowed values for pagination in API requests.
 * These values control how many items are returned per page.
 * 
 * @package LJPc\ClouDNS\Enums
 * @author LJPC
 * @since 1.0.0
 */
enum RowsPerPage: int
{
    case TEN = 10;
    case TWENTY = 20;
    case THIRTY = 30;
    case FIFTY = 50;
    case HUNDRED = 100;

    /**
     * Get all rows per page values as an array
     * 
     * @return array<int, int> Array of all pagination values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a rows per page value is valid
     * 
     * @param int $rows The number of rows to validate
     * @return bool True if the value is valid
     */
    public static function isValid(int $rows): bool
    {
        return in_array($rows, self::values(), true);
    }

    /**
     * Get the default rows per page value
     * 
     * @return self The default value (30 rows)
     */
    public static function getDefault(): self
    {
        return self::THIRTY;
    }
}