<?php

use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Support\AvailabilityResolver;

/**
 * Build an AvailabilityResponse with one or more category metric blocks.
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

it('returns false when no categories present', function () {
    $response = AvailabilityResponse::map(['TimeUnitStartsUtc' => [], 'ResourceCategoryAvailabilities' => []]);
    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns true when target category has free capacity on every night', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z', '2026-06-16T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3, 3], 'occupied' => [2, 1, 0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when ANY night has zero free capacity (the regression we are fixing)', function () {
    // pid=8 / 2026-06-25→2026-06-28 production scenario: [0, 0, 0, 1] free
    $response = makeAvailabilityResponse(
        ['2026-06-24T22:00:00Z', '2026-06-25T22:00:00Z', '2026-06-26T22:00:00Z', '2026-06-27T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3, 3, 3], 'occupied' => [3, 3, 3, 2]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns false when category matches but a middle night is fully booked', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z', '2026-06-16T22:00:00Z'],
        ['cat-1' => ['usable' => [3, 3, 3], 'occupied' => [1, 3, 1]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns false when target category is not present in response', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z'],
        ['cat-1' => ['usable' => [3], 'occupied' => [0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-MISSING'))->toBeFalse();
});

it('errs on the safe side — subtracts OutOfOrderBlocks even though Usable already encodes them', function () {
    // Live verification revealed Mews itself can refuse reservations/add even when
    // category-level metrics suggest there is room. We can't reproduce per-resource
    // continuity from the category metrics, so we keep the conservative OOB
    // subtraction to avoid promising availability that the PMS will later reject.
    // pid=5 / d+15 production: Usable=2 OOB=1 Occupied=1 — Mews refused booking.
    $response = makeAvailabilityResponse(
        ['2026-06-15T22:00:00Z'],
        ['cat-1' => ['usable' => [2], 'occupied' => [1], 'outOfOrder' => [1]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('returns false when UsableResources is already zero (fully OOO blocked)', function () {
    // pid=22 production scenario: Active=[2] OOB=[2] Usable=[0] Occupied=[0].
    $response = makeAvailabilityResponse(
        ['2026-07-30T22:00:00Z'],
        ['cat-1' => ['usable' => [0, 0], 'occupied' => [0, 0], 'outOfOrder' => [2, 2]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('case-insensitively matches category id', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z'],
        ['CAT-1' => ['usable' => [3], 'occupied' => [0]]]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeTrue();
});

it('returns false when metrics arrays are empty (no UsableResources data)', function () {
    $response = AvailabilityResponse::map([
        'TimeUnitStartsUtc' => ['2026-06-14T22:00:00Z'],
        'ResourceCategoryAvailabilities' => [[
            'ResourceCategoryId' => 'cat-1',
            'Metrics' => [],
        ]],
    ]);

    expect(AvailabilityResolver::isFullyAvailable($response, 'cat-1'))->toBeFalse();
});

it('without category filter requires EVERY category to be fully available', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z'],
        [
            'cat-1' => ['usable' => [3, 3], 'occupied' => [1, 1]],
            'cat-2' => ['usable' => [3, 3], 'occupied' => [3, 1]], // night 0 sold out
        ]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, null))->toBeFalse();
});

it('without category filter returns true when every category is fully available', function () {
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z', '2026-06-15T22:00:00Z'],
        [
            'cat-1' => ['usable' => [3, 3], 'occupied' => [1, 1]],
            'cat-2' => ['usable' => [3, 3], 'occupied' => [0, 2]],
        ]
    );

    expect(AvailabilityResolver::isFullyAvailable($response, null))->toBeTrue();
});
