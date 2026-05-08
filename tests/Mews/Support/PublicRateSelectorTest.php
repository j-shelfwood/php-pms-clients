<?php

use Shelfwood\PhpPms\Mews\Enums\RateType;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;
use Shelfwood\PhpPms\Mews\Support\PublicRateSelector;

function makeRate(
    string $id,
    bool $isActive = true,
    bool $isPublic = true,
    float $relative = 0.0,
    float $absolute = 0.0
): Rate {
    return new Rate(
        id: $id,
        serviceId: 'svc-1',
        groupId: null,
        accountingCategoryId: null,
        isActive: $isActive,
        isPublic: $isPublic,
        type: RateType::Public,
        names: ['en' => $id],
        shortNames: null,
        description: null,
        baseRateId: null,
        isPrivate: false,
        externalIdentifier: null,
        createdUtc: '',
        updatedUtc: '',
        relativeAdjustment: $relative,
        absoluteAdjustment: $absolute,
    );
}

it('returns null for empty input', function () {
    expect(PublicRateSelector::cheapest([]))->toBeNull();
});

it('returns null when all rates are inactive', function () {
    $rates = [makeRate('r1', isActive: false), makeRate('r2', isActive: false)];
    expect(PublicRateSelector::cheapest($rates))->toBeNull();
});

it('skips non-public rates', function () {
    $rates = [
        makeRate('private', isPublic: false, relative: -0.5),
        makeRate('public', isPublic: true, relative: 0.0),
    ];
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('public');
});

it('picks the rate with the lowest relativeAdjustment + absoluteAdjustment', function () {
    $rates = [
        makeRate('standard', relative: 0.0, absolute: 0.0),
        makeRate('non-refundable', relative: -0.07, absolute: 0.0),
        makeRate('seven-day-notice', relative: 0.07, absolute: 0.0),
    ];
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('non-refundable');
});

it('considers absoluteAdjustment in ranking', function () {
    $rates = [
        makeRate('a', relative: 0.0, absolute: 5.0),
        makeRate('b', relative: 0.0, absolute: -2.0),
        makeRate('c', relative: -0.05, absolute: 0.0),
    ];
    // Scores: a=5, b=-2, c=-0.05 → b wins
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('b');
});

it('accepts a Laravel Collection', function () {
    $rates = collect([
        makeRate('a', relative: 0.0),
        makeRate('b', relative: -0.1),
    ]);
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('b');
});

it('accepts a generator', function () {
    $gen = (function () {
        yield makeRate('a', relative: 0.2);
        yield makeRate('b', relative: -0.3);
        yield makeRate('c', relative: 0.0);
    })();
    expect(PublicRateSelector::cheapest($gen)->id)->toBe('b');
});

it('ignores non-Rate items defensively', function () {
    $rates = [makeRate('r', relative: 0.0), 'not-a-rate', null, 42];
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('r');
});

it('returns the first when multiple rates tie on score', function () {
    $rates = [
        makeRate('first', relative: 0.0),
        makeRate('second', relative: 0.0),
    ];
    // First wins because the comparator uses strict `<`
    expect(PublicRateSelector::cheapest($rates)->id)->toBe('first');
});
