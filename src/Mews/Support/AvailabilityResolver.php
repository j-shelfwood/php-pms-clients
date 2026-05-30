<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;

/**
 * AvailabilityResolver — pure helper for interpreting Mews availability metrics.
 *
 * Returns true only when EVERY billable night in the queried window has at least
 * one free resource in the target category.
 *
 * ## Free-resource formula
 *
 * Per Mews's own spec ("Service availability metrics, ver 2024-01-22"):
 *
 *   UsableResources = "Number of usable resources (i.e. which are not out of order)"
 *   Occupied        = "Number of bookings that have been assigned to the resource category"
 *
 * Therefore:    free = UsableResources − Occupied
 *
 * Empirically verified across 290 live samples from the Stayci production tenant
 * (29 categories × 10 windows): the identities `Usable = Active − OOB` and
 * `Occupied = Confirmed + Optional` hold without exception. Subtracting OOB
 * again would be double-counting and produced false negatives on every
 * category with OOB > 0 (~7 of 29 Stayci categories).
 *
 * ## N+1 endpoint convention
 *
 * Mews getAvailability returns N+1 time-unit entries for an N-night stay where
 * the last entry corresponds to the CHECKOUT DAY (the day the guest leaves;
 * not a billable night). Example: a stay 2026-07-30 → 2026-08-02 returns 4
 * entries mapped to [night1, night2, night3, checkout-day]. The pricing
 * transformer slices [0..N) for the same reason — see N+1 handling in
 * MewsPricingTransformer.
 *
 * We drop the last index here so the resolver only checks nights the guest
 * actually occupies. Without this, a fully-booked checkout day (next stay
 * starts that day) wrongly hid the property from quote.
 *
 * ## What this resolver does NOT cover
 *
 * Even when this returns true, Mews `reservations/add` can still 403 because of:
 *  - Per-resource continuity: a 3-night stay needs ONE specific room free
 *    across all nights. Category-level metrics expose only aggregate counts.
 *    Mews doesn't surface per-resource availability in this endpoint.
 *  - Rate restrictions: `minAdvance`, `minLength`, etc. live in restrictions/getAll.
 *    Filter rates upstream of the booking call.
 *  - AvailabilityBlock / AllocatedBlockAvailability: rare on standard inventory
 *    but possible on partner-allotment channels.
 *
 * Callers MUST still handle reservations/add 403 "no availability" responses
 * — this resolver only catches the cases where category metrics themselves
 * preclude a booking.
 */
class AvailabilityResolver
{
    /**
     * Check whether a specific resource category has at least one free resource on
     * every billable night returned by getAvailability.
     *
     * Returns false when:
     *  - no category matches the optional $resourceCategoryId filter
     *  - any billable night has Usable − Occupied ≤ 0
     *  - the response has zero time-units, or only the checkout-day entry
     *
     * When $resourceCategoryId is null, ALL categories in the response must be
     * fully available (rare — callers should normally scope to one category).
     */
    public static function isFullyAvailable(
        AvailabilityResponse $availability,
        ?string $resourceCategoryId = null
    ): bool {
        $categories = $availability->resourceCategoryAvailabilities;

        if ($categories->isEmpty()) {
            return false;
        }

        // For an N-night stay Mews returns N+1 entries — drop the last
        // (checkout-day). For a 1-entry response there are no billable nights.
        $billableNightCount = count($availability->timeUnitStartsUtc) - 1;
        if ($billableNightCount <= 0) {
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

            if (! self::categoryFullyAvailable($category, $billableNightCount)) {
                return false;
            }
        }

        return $matched;
    }

    /**
     * Per-category check: every billable-night index must have Usable − Occupied > 0.
     *
     * We do NOT subtract OutOfOrderBlocks again because UsableResources already
     * nets OOB per Mews's spec.
     */
    private static function categoryFullyAvailable(
        ResourceCategoryAvailability $category,
        int $billableNightCount
    ): bool {
        $metrics = $category->metrics;
        $usable = $metrics[ResourceAvailabilityMetricType::UsableResources->value] ?? [];
        $occupied = $metrics[ResourceAvailabilityMetricType::Occupied->value] ?? [];

        if ($usable === [] || count($usable) < $billableNightCount) {
            return false;
        }

        for ($i = 0; $i < $billableNightCount; $i++) {
            $free = $usable[$i] - ($occupied[$i] ?? 0);
            if ($free <= 0) {
                return false;
            }
        }

        return true;
    }
}
