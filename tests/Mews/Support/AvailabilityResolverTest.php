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

it('subtracts OutOfOrderBlocks from free capacity', function () {
    // Usable=3, Occupied=1, OutOfOrder=2 → free=0 → unavailable
    $response = makeAvailabilityResponse(
        ['2026-06-14T22:00:00Z'],
        ['cat-1' => ['usable' => [3], 'occupied' => [1], 'outOfOrder' => [2]]]
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
