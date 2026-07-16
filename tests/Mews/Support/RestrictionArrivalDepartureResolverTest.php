<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;
use Shelfwood\PhpPms\Mews\Support\RestrictionArrivalDepartureResolver;

function adRestriction(array $conditions, array $exceptions = []): Restriction
{
    return Restriction::map([
        'Id' => 'r-1',
        'ServiceId' => 'svc-1',
        'Conditions' => array_merge([
            'ResourceCategoryId' => 'cat-1',
            'StartUtc' => '2025-05-01T00:00:00Z',
            'EndUtc' => '2025-07-01T00:00:00Z',
            'Days' => [],
            'Hours' => [],
        ], $conditions),
        'Exceptions' => $exceptions,
    ]);
}

it('reports no closure when no restrictions provided', function () {
    expect(RestrictionArrivalDepartureResolver::isClosedToArrival([], Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse()
        ->and(RestrictionArrivalDepartureResolver::isClosedToDeparture([], Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse();
});

it('closes arrival on a matching Start restriction', function () {
    $restrictions = [adRestriction(['Type' => 'Start'])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeTrue();
});

it('closes departure on a matching End restriction', function () {
    $restrictions = [adRestriction(['Type' => 'End'])];

    expect(RestrictionArrivalDepartureResolver::isClosedToDeparture($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeTrue();
});

it('does not treat a Start restriction as a departure closure (and vice versa)', function () {
    $start = [adRestriction(['Type' => 'Start'])];
    $end = [adRestriction(['Type' => 'End'])];

    expect(RestrictionArrivalDepartureResolver::isClosedToDeparture($start, Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse()
        ->and(RestrictionArrivalDepartureResolver::isClosedToArrival($end, Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse();
});

it('ignores Stay restrictions for arrival/departure', function () {
    $restrictions = [adRestriction(['Type' => 'Stay'], ['MinLength' => 'P0M3DT0H0M0S'])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse()
        ->and(RestrictionArrivalDepartureResolver::isClosedToDeparture($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeFalse();
});

it('does not close arrival outside the restriction date range', function () {
    $restrictions = [adRestriction([
        'Type' => 'Start',
        'StartUtc' => '2025-12-20T00:00:00Z',
        'EndUtc' => '2026-01-05T00:00:00Z',
    ])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-11-01'), 'cat-1'))->toBeFalse();
});

it('does not close arrival when category does not match', function () {
    $restrictions = [adRestriction(['Type' => 'Start'])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-01'), 'cat-2'))->toBeFalse();
});

it('applies an open-ended (null start/end, null category) Start restriction to all dates and categories', function () {
    $restrictions = [adRestriction([
        'Type' => 'Start',
        'ResourceCategoryId' => null,
        'StartUtc' => null,
        'EndUtc' => null,
    ])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2030-06-15'), 'any-category'))->toBeTrue();
});

it('honours a weekday Days filter in list form (only the listed weekday is closed)', function () {
    // 2025-06-07 is a Saturday; 2025-06-08 is a Sunday.
    $restrictions = [adRestriction(['Type' => 'Start', 'Days' => ['Saturday']])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-07'), 'cat-1'))->toBeTrue()
        ->and(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-08'), 'cat-1'))->toBeFalse();
});

it('honours a weekday Days filter in Mews boolean-map form', function () {
    // Wire format: {"Saturday": true, "Sunday": false, ...}
    $restrictions = [adRestriction([
        'Type' => 'Start',
        'Days' => ['Saturday' => true, 'Sunday' => false],
    ])];

    expect(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-07'), 'cat-1'))->toBeTrue()
        ->and(RestrictionArrivalDepartureResolver::isClosedToArrival($restrictions, Carbon::parse('2025-06-08'), 'cat-1'))->toBeFalse();
});
