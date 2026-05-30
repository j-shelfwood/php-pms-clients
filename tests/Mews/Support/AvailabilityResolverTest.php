<?php

use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Support\AvailabilityResolver;

/**
 * Build an AvailabilityResponse with one or more category metric blocks.
 *
 * Convention: $timeUnitStartsUtc length is N+1 for an N-night stay (Mews's
 * own indexing — the last entry corresponds to the checkout day).
 *
 * @param array<string, array{usable: array<int>, occupied: array<int>, outOfOrder?: array<int>}> $categories
 */
function makeAvailabilityResponse(array $timeUnitStartsUtc, array $categories): AvailabilityResponse
{
    $blocks = [];
    foreach ($categories as $categoryId => $metrics) {
        $blocks[] = [
            'ResourceCategoryId' => $categoryId,
            'Metrics' => [
                'UsableResources' => $metrics['usable'],
                'Occupied' => $metrics['occupied'],
                'OutOfOrderBlocks' => $metrics['outOfOrder'] ?? array_fill(0, count($metrics['usable']), 0),
                'ConfirmedReservations' => $metrics['occupied'],
                'OptionalReservations' => array_fill(0, count($metrics['usable']), 0),
            ],
        ];
    }

    return AvailabilityResponse::map([
        'TimeUnitStartsUtc' => $timeUnitStartsUtc,
        'ResourceCategoryAvailabilities' => $blocks,
    ]);
}

// ----- core formula -----

it('returns false when no categories present', function () {
    $response = AvailabilityResponse::map(['TimeUnitStartsUtc' => [], 'ResourceCategoryAvailabilities' => []]);
    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns true when every billable night has free capacity (Usable − Occupied > 0)', function () {
    // 3-night stay → 4 time-units. Free on every billable night.
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z', '2026-06-16T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3, 3, 3], 'occupied' => [2, 1, 0, 1]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when ANY billable night has zero free capacity', function () {
    // Middle night sold out.
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z', '2026-06-16T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3, 3, 3], 'occupied' => [1, 3, 1, 1]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns false when target category is not present in response', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3], 'occupied' => [0, 0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-MISSING'))->toBeFalse();
});

it('case-insensitively matches category id', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z'],
        ['CAT-1' => ['usable' => [3, 3], 'occupied' => [0, 0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when metrics arrays are empty (no UsableResources data)', function () {
    $response = AvailabilityResponse::map([
        'TimeUnitStartsUtc' => ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z'],
        'ResourceCategoryAvailabilities' => [[
            'ResourceCategoryId' => 'cat-1',
            'Metrics' => [],
        ]],
    ]);

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

// ----- N+1 endpoint convention -----

it('drops the last time-unit (checkout day) when judging bookability', function () {
    // 2-night stay → 3 entries. Last entry = checkout day, fully occupied because
    // next stay arrives that day. Should NOT block this stay.
    // Verified empirically: stayci pid=2 d+60 (U=[2,2,2,2] Occ=[0,0,1,2]) accepted by Mews.
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z'],
        ['cat-1' => ['usable' => [1, 1, 1], 'occupied' => [0, 0, 1]]] // checkout day occupied
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when time-units array has only the checkout-day entry (no billable nights)', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z'],
        ['cat-1' => ['usable' => [1], 'occupied' => [0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

// ----- OOB semantics (spec-confirmed: UsableResources already nets OOB) -----

it('does NOT subtract OutOfOrderBlocks again — UsableResources is already net of OOB per spec', function () {
    // Mews spec: UsableResources = "Number of usable resources (i.e. which are not out of order)"
    // Live verification: stayci pid=13 / d+45 (Active=[4] OOB=[2] Usable=[2] Occupied=[1])
    // was successfully booked. Our resolver must return true.
    $response = makeAvailabilityResponse(
        ['2026-07-29T22:00:00Z', '2026-07-30T22:00:00Z'],
        ['cat-1' => ['usable' => [2, 2], 'occupied' => [1, 1], 'outOfOrder' => [2, 2]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when UsableResources is zero (fully OOO blocked at source)', function () {
    // pid=22 production scenario: Active=[2] OOB=[2] Usable=[0] Occupied=[0].
    // Mews already encoded the unavailability in Usable=0; we just respect it.
    $response = makeAvailabilityResponse(
        ['2026-07-29T22:00:00Z', '2026-07-30T22:00:00Z'],
        ['cat-1' => ['usable' => [0, 0], 'occupied' => [0, 0], 'outOfOrder' => [2, 2]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

// ----- multi-category fallback -----

it('without category filter requires EVERY category to be fully available', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z'],
        [
            'cat-1' => ['usable' => [3, 3, 3], 'occupied' => [1, 1, 0]],
            'cat-2' => ['usable' => [3, 3, 3], 'occupied' => [3, 1, 0]], // night 0 sold out
        ]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, null))->toBeFalse();
});

it('without category filter returns true when every category is fully available on billable nights', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-13T22:00:00Z', '2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z'],
        [
            'cat-1' => ['usable' => [3, 3, 3], 'occupied' => [1, 1, 0]],
            'cat-2' => ['usable' => [3, 3, 3], 'occupied' => [0, 2, 0]],
        ]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, null))->toBeTrue();
});
