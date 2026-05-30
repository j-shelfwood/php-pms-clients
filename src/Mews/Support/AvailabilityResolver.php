<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;

/**
 * AvailabilityResolver — pure helper for interpreting Mews availability metrics.
 *
 * Returns true only when EVERY night in the queried window has at least one
 * apparently-free resource in the target category. This is the conservative
 * "category-level free count" check (Usable − Occupied − OutOfOrderBlocks > 0).
 *
 * KNOWN LIMITATIONS — read before relying on a true verdict:
 *
 *  1. False negatives possible. UsableResources is already net of OOB per Mews,
 *     so subtracting OOB again can hide categories Mews would actually accept.
 *     Empirically (pid=13 d+30: Usable=2 OOB=2 Occupied=0), Mews accepts the
 *     booking even though this helper reports false. We choose the safe side
 *     here: a missed sale beats a charged-but-rejected booking.
 *
 *  2. False positives still possible. Even when Usable − Occupied − OOB > 0
 *     across the window, Mews can still refuse reservations/add because:
 *       - Per-resource continuity: a 3-night stay needs ONE specific room free
 *         on all 3 nights; this metric is category-level and doesn't reveal
 *         room rotations within the window.
 *       - Rate restrictions: minAdvance/minLength on the selected rate.
 *       - PublicAvailabilityAdjustment, BlockAvailability, AllocatedBlockAvailability
 *         applied at booking time but not modeled here.
 *
 * Callers MUST still handle reservations/add 403 "no availability" responses.
 * This helper only catches the obvious "every night has zero free" cases that
 * previously caused quote endpoints to lie before the customer entered payment.
 *
 * Historical bug (pre-fix): callers used "any night has capacity" → quote endpoint
 * returned available=true even when every night except one was sold out.
 */
class AvailabilityResolver
{
    /**
     * Check whether a specific resource category has at least one apparently-free
     * resource on EVERY night returned by getAvailability.
     *
     * Returns false when:
     *  - no category matches the optional $resourceCategoryId filter
     *  - any night has Usable − Occupied − OutOfOrderBlocks ≤ 0
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
     * Free = Usable − Occupied − OutOfOrderBlocks. This double-counts OOB
     * vs. how Mews internally computes Usable, but errs on the safe side:
     * the resolver may say "unavailable" when Mews would have accepted,
     * but it never says "available" purely on category metrics when those
     * indicate a sold-out night. False positives can still arise from
     * per-resource continuity rules — see class-level docblock.
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
