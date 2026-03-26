<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;
use Shelfwood\PhpPms\Mews\Support\RestrictionMinStayResolver;

it('returns null when no restrictions provided', function () {
    expect(RestrictionMinStayResolver::resolveForDate([], Carbon::parse('2025-06-01'), 'cat-1'))->toBeNull();
});

it('returns null when no Stay-type restrictions apply', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Start',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-05-01T00:00:00Z',
                'EndUtc' => '2025-07-01T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M5DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeNull();
});

it('returns minimum stay from matching Stay restriction', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-05-01T00:00:00Z',
                'EndUtc' => '2025-07-01T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBe(3);
});

it('returns most restrictive stay when multiple Stay restrictions overlap', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-06-01T00:00:00Z',
                'EndUtc' => '2025-07-01T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
        Restriction::map([
            'Id' => 'r-2',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-06-10T00:00:00Z',
                'EndUtc' => '2025-06-20T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M5DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-06-15'), 'cat-1'))->toBe(5);
});

it('returns null when date is outside restriction range', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-11-01'), 'cat-1'))->toBeNull();
});

it('returns null when category does not match', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-12-25'), 'cat-2'))->toBeNull();
});

it('applies open-ended restrictions with null startUtc and endUtc to all dates and categories', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => null,
                'StartUtc' => null,
                'EndUtc' => null,
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M7DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2030-06-15'), 'any-category'))->toBe(7);
});

it('ignores End-type restrictions when computing minimum stay', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'r-1',
            'ServiceId' => 'svc-1',
            'Conditions' => [
                'Type' => 'End',
                'ResourceCategoryId' => 'cat-1',
                'StartUtc' => '2025-05-01T00:00:00Z',
                'EndUtc' => '2025-07-01T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M5DT0H0M0S'],
        ]),
    ];

    expect(RestrictionMinStayResolver::resolveForDate($restrictions, Carbon::parse('2025-06-01'), 'cat-1'))->toBeNull();
});
