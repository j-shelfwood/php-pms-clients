<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\Mews\Concerns;

/**
 * Extracts reservation date fields with proper fallback for API versioning
 *
 * Mews Connector API evolved over time:
 * - Modern API: Uses ScheduledStartUtc and ScheduledEndUtc for reservation dates
 * - Legacy API: Uses StartUtc and EndUtc (deprecated but still returned)
 *
 * This trait encapsulates the fallback logic: prefer scheduled fields, fallback to deprecated fields.
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/reservations
 */
trait ExtractsReservationDates
{
    /**
     * Extract reservation start date with proper fallback
     *
     * Priority: ScheduledStartUtc (modern) → StartUtc (deprecated)
     *
     * @param array $reservation Mews reservation object
     * @return string|null ISO 8601 UTC datetime string
     */
    protected function extractReservationStartDate(array $reservation): ?string
    {
        return $reservation['ScheduledStartUtc'] ?? $reservation['StartUtc'] ?? null;
    }

    /**
     * Extract reservation end date with proper fallback
     *
     * Priority: ScheduledEndUtc (modern) → EndUtc (deprecated)
     *
     * @param array $reservation Mews reservation object
     * @return string|null ISO 8601 UTC datetime string
     */
    protected function extractReservationEndDate(array $reservation): ?string
    {
        return $reservation['ScheduledEndUtc'] ?? $reservation['EndUtc'] ?? null;
    }

    /**
     * Extract both reservation dates as associative array
     *
     * Convenience method for extracting both dates at once.
     *
     * @param array $reservation Mews reservation object
     * @return array{start: string|null, end: string|null}
     */
    protected function extractReservationDates(array $reservation): array
    {
        return [
            'start' => $this->extractReservationStartDate($reservation),
            'end' => $this->extractReservationEndDate($reservation),
        ];
    }
}
