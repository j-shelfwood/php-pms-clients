<?php

namespace Shelfwood\PhpPms\Mews\Services;

use Carbon\Carbon;

/**
 * Date Converter for Mews API Requirements
 *
 * Mews API requires FirstTimeUnitStartUtc/LastTimeUnitStartUtc to be
 * "00:00 converted to UTC" - meaning midnight in the enterprise's local
 * timezone, expressed as UTC.
 *
 * EXAMPLE:
 * Enterprise timezone: Europe/Budapest (UTC+1 in winter, UTC+2 in summer)
 * Input: 2024-11-01 00:00:00 UTC
 * Output: 2024-10-31 23:00:00 UTC (midnight Budapest time, expressed as UTC)
 *
 * ALGORITHM:
 * 1. Take input date in UTC
 * 2. Convert to enterprise timezone (preserves same instant in time)
 * 3. Set time to midnight (00:00:00) in enterprise timezone
 * 4. Convert back to UTC (now represents midnight enterprise time as UTC)
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/services#get-service-availability
 */
class DateConverter
{
    /**
     * Convert a date to midnight in enterprise timezone, expressed as UTC
     *
     * This produces the date format required by Mews API for time unit boundaries.
     *
     * @param Carbon $date Input date (typically in UTC)
     * @param string $enterpriseTimezone IANA timezone identifier (e.g., "Europe/Budapest")
     * @return Carbon Date at midnight in enterprise timezone, expressed as UTC
     *
     * @example
     * $utcDate = Carbon::parse('2024-11-01 00:00:00 UTC');
     * $converted = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');
     * // Result: 2024-10-31 23:00:00 UTC (midnight Nov 1 Budapest = 23:00 Oct 31 UTC)
     */
    public static function toEnterpriseMidnightUtc(Carbon $date, string $enterpriseTimezone): Carbon
    {
        // Create a new Carbon instance to avoid mutating the input
        return $date->copy()
            // Convert to enterprise timezone
            ->setTimezone($enterpriseTimezone)
            // Set to midnight in enterprise timezone
            ->startOfDay()
            // Convert back to UTC (now represents midnight enterprise time as UTC)
            ->utc();
    }

    /**
     * Convert a date range to enterprise timezone midnight boundaries
     *
     * Convenience method for converting both start and end dates.
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @param string $enterpriseTimezone IANA timezone identifier
     * @return array{start: Carbon, end: Carbon} Converted dates
     */
    public static function convertDateRange(Carbon $startDate, Carbon $endDate, string $enterpriseTimezone): array
    {
        return [
            'start' => self::toEnterpriseMidnightUtc($startDate, $enterpriseTimezone),
            'end' => self::toEnterpriseMidnightUtc($endDate, $enterpriseTimezone),
        ];
    }
}
