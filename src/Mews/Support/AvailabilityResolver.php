<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;

/**
 * AvailabilityResolver — pure helper for interpreting Mews availability metrics.
 *
 * Mews returns per-night arrays: UsableResources, Occupied, OutOfOrderBlocks, etc.
 * A category is bookable only when EVERY night in the queried window has at least
 * one free resource (Usable − Occupied − OutOfOrderBlocks > 0).
 *
 * Historical bug (pre-fix): callers used "any night has capacity" → quote endpoint
 * returned available=true even when reservations/add would 403 because one of the
 * middle nights was fully occupied. That mismatch confused users and was a co-cause
 * of the original ghost-stay symptom.
 */
class AvailabilityResolver
{
    /**
     * Check whether a specific resource category has at least one free resource on
     * EVERY night returned by getAvailability.
     *
     * Returns false when:
     *  - no category matches the optional $resourceCategoryId filter
     *  - any night has 0 free resources after subtracting Occupied + OutOfOrderBlocks
     *  - metrics arrays are empty (treat as unknown → not available)
     *
     * When $resourceCategoryId is null, ALL categories in the response must be fully
     * available (rare — callers should normally scope to one category).
     */
    public static function isFullyAvailable(
        AvailabilityResponse $availability,
        ?string $resourceCategoryId = null
    ): bool {
        $categories = $availability->resourceCategoryAvailabilities;

        if ($categories->isEmpty()) {
            return false;
        }

        $matched = false;

        foreach ($categories as $category) {
            if (! $category instanceof ResourceCategoryAvailability) {
                continue;
            }

            if ($resourceCategoryId !== null
                && strcasecmp($category->resourceCategoryId, $resourceCategoryId) !== 0
            ) {
                continue;
            }

            $matched = true;

            if (! self::categoryFullyAvailable($category)) {
                return false;
            }
        }

        return $matched;
    }

    /**
     * Per-category check: every index of UsableResources must yield > 0 free.
     *
     * "Free" = Usable − Occupied − OutOfOrderBlocks. We do NOT subtract
     * OptionalReservations because Occupied already includes them per Mews docs.
     */
    private static function categoryFullyAvailable(ResourceCategoryAvailability $category): bool
    {
        $metrics = $category->metrics;
        $usable = $metrics[ResourceAvailabilityMetricType::UsableResources->value] ?? [];
        $occupied = $metrics[ResourceAvailabilityMetricType::Occupied->value] ?? [];
        $outOfOrder = $metrics[ResourceAvailabilityMetricType::OutOfOrderBlocks->value] ?? [];

        if ($usable === []) {
            return false;
        }

        foreach ($usable as $index => $usableCount) {
            $free = $usableCount
                - ($occupied[$index] ?? 0)
                - ($outOfOrder[$index] ?? 0);

            if ($free <= 0) {
                return false;
            }
        }

        return true;
    }
}
