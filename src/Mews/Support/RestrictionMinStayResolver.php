<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;

/**
 * RestrictionMinStayResolver - Pure static helper for resolving minimum stay from Mews restrictions.
 *
 * Only Stay-type restrictions govern minimum stay length. Start/End restrictions
 * control check-in/check-out day eligibility and do not affect stay_minimum.
 *
 * Returns the most restrictive (longest) minimum stay when multiple restrictions apply.
 * Handles open-ended restrictions (null startUtc/endUtc = applies indefinitely to all dates/categories).
 */
class RestrictionMinStayResolver
{
    /**
     * Resolve the minimum stay requirement for a specific date and resource category.
     *
     * @param array<Restriction> $restrictions All restriction objects to evaluate
     * @param Carbon $date Date to check
     * @param string|null $resourceCategoryId Resource category UUID (null = apply all-category restrictions only)
     * @return int|null Most restrictive minimum stay in nights, or null if none found
     */
    public static function resolveForDate(array $restrictions, Carbon $date, ?string $resourceCategoryId = null): ?int
    {
        $maxNights = null;

        foreach ($restrictions as $restriction) {
            $conditions = $restriction->conditions;

            // Only Stay-type restrictions govern minimum stay length
            if ($conditions->type->value !== 'Stay') {
                continue;
            }

            // Filter by category — null means applies to all categories
            if ($conditions->resourceCategoryId !== null && $conditions->resourceCategoryId !== $resourceCategoryId) {
                continue;
            }

            // Handle open-ended restrictions (null start/end = applies indefinitely)
            $start = $conditions->startUtc !== null ? Carbon::parse($conditions->startUtc) : null;
            $end = $conditions->endUtc !== null ? Carbon::parse($conditions->endUtc) : null;

            $inRange = ($start === null || $start->lte($date)) && ($end === null || $end->gte($date));

            if (! $inRange) {
                continue;
            }

            // MinLength is ISO 8601 duration e.g. "P0M3DT0H0M0S" = 3 days/nights
            $minLength = $restriction->exceptions->minLength ?? null;
            if ($minLength === null) {
                continue;
            }

            try {
                $nights = (int) (new \DateInterval($minLength))->d;
            } catch (\Exception) {
                continue;
            }

            if ($nights > 0 && ($maxNights === null || $nights > $maxNights)) {
                $maxNights = $nights;
            }
        }

        return $maxNights;
    }
}
