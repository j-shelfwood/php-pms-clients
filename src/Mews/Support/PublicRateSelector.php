<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;

/**
 * PublicRateSelector — pure helper for picking the cheapest active+public rate.
 *
 * Considers both base rates (adjustment=0) and dependent rates (e.g. Non Refundable=-7%).
 * Uses relativeAdjustment + absoluteAdjustment from the rates/getAll Pricing field to rank
 * rates without requiring extra API calls. The rate with the lowest combined adjustment wins.
 *
 * Example: Non Refundable (relativeAdjustment=-0.07) beats Standard (0.0) beats 7-day notice (+0.07).
 */
class PublicRateSelector
{
    /**
     * Select the cheapest active+public rate from a collection of rates.
     *
     * Ranking is by `relativeAdjustment + absoluteAdjustment` ascending — most negative
     * (= biggest discount) wins. Inactive rates and non-public rates are filtered out first.
     *
     * @param iterable<Rate> $rates Rates to consider — accepts arrays, Collections, generators
     * @return Rate|null Cheapest active+public rate, or null if none qualify
     */
    public static function cheapest(iterable $rates): ?Rate
    {
        $best = null;
        $bestScore = null;

        foreach ($rates as $rate) {
            if (!$rate instanceof Rate) {
                continue;
            }
            if (!$rate->isActive || !$rate->isPublic) {
                continue;
            }

            $score = $rate->relativeAdjustment + $rate->absoluteAdjustment;

            if ($bestScore === null || $score < $bestScore) {
                $best = $rate;
                $bestScore = $score;
            }
        }

        return $best;
    }
}
