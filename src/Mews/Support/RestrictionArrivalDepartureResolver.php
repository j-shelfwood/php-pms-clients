<?php

namespace Shelfwood\PhpPms\Mews\Support;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;

/**
 * RestrictionArrivalDepartureResolver - Pure static helper for resolving check-in /
 * check-out eligibility from Mews restrictions.
 *
 * Per the Mews Connector API (docs.mews.com/connector-api/operations/restrictions):
 *   - Type=Start => "Guests can't check in within specified dates"  (ClosedToArrival)
 *   - Type=End   => "Guests can't check out within specified dates" (ClosedToDeparture)
 *   - Type=Stay  => length-of-stay (handled by RestrictionMinStayResolver)
 *
 * A restriction applies to a given date when ALL of the following hold:
 *   - the type matches (Start for arrival, End for departure)
 *   - the resource category matches (null on the restriction = applies to all categories)
 *   - the date falls within [startUtc, endUtc] (null bounds = indefinite)
 *   - the date's weekday is selected by the Days filter (empty Days = every weekday)
 *
 * Mirrors RestrictionMinStayResolver's category/date/open-ended semantics; the weekday
 * (Days) check is the only addition — Mews scopes ClosedToArrival/Departure rules to
 * specific weekdays (e.g. "no arrivals on Saturdays").
 */
class RestrictionArrivalDepartureResolver
{
    /**
     * Is check-in (arrival) closed on this date for this category?
     *
     * @param array<Restriction> $restrictions All restriction objects to evaluate
     * @param Carbon $date Prospective check-in date
     * @param string|null $resourceCategoryId Resource category UUID (null = all-category restrictions only)
     */
    public static function isClosedToArrival(array $restrictions, Carbon $date, ?string $resourceCategoryId = null): bool
    {
        return self::isClosedForType($restrictions, $date, $resourceCategoryId, 'Start');
    }

    /**
     * Is check-out (departure) closed on this date for this category?
     *
     * @param array<Restriction> $restrictions All restriction objects to evaluate
     * @param Carbon $date Prospective check-out date
     * @param string|null $resourceCategoryId Resource category UUID (null = all-category restrictions only)
     */
    public static function isClosedToDeparture(array $restrictions, Carbon $date, ?string $resourceCategoryId = null): bool
    {
        return self::isClosedForType($restrictions, $date, $resourceCategoryId, 'End');
    }

    private static function isClosedForType(array $restrictions, Carbon $date, ?string $resourceCategoryId, string $type): bool
    {
        foreach ($restrictions as $restriction) {
            $conditions = $restriction->conditions;

            if ($conditions->type->value !== $type) {
                continue;
            }

            // null category on the restriction = applies to all categories
            if ($conditions->resourceCategoryId !== null && $conditions->resourceCategoryId !== $resourceCategoryId) {
                continue;
            }

            $start = $conditions->startUtc !== null ? Carbon::parse($conditions->startUtc) : null;
            $end = $conditions->endUtc !== null ? Carbon::parse($conditions->endUtc) : null;

            $inRange = ($start === null || $start->lte($date)) && ($end === null || $end->gte($date));

            if (! $inRange) {
                continue;
            }

            if (! self::dayMatches($conditions->days, $date)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Does the Days filter select this date's weekday?
     *
     * Empty Days = every weekday is restricted (no weekday narrowing).
     *
     * Mews' wire format is a boolean map keyed by weekday name
     * ({"Monday":false,...}); some callers/mappers normalise it to a list of
     * the active weekday names (["Saturday"]). Accept both shapes:
     *   - list form:   the weekday name is present as a value
     *   - map form:    the weekday name is present as a key with a truthy value
     *
     * @param array<int|string, mixed> $days
     */
    private static function dayMatches(array $days, Carbon $date): bool
    {
        if ($days === []) {
            return true;
        }

        $weekday = $date->format('l'); // Monday, Tuesday, ...

        // Map form: {"Saturday": true, ...}
        if (array_key_exists($weekday, $days)) {
            return (bool) $days[$weekday];
        }

        // List form: ["Saturday", ...]
        return in_array($weekday, $days, true);
    }
}
