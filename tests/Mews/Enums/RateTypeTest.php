<?php

use Shelfwood\PhpPms\Mews\Enums\RateType;

it('has all rate types', function () {
    expect(RateType::cases())->toHaveCount(3)
        ->and(RateType::Public->value)->toBe('Public')
        ->and(RateType::Private->value)->toBe('Private')
        ->and(RateType::AvailabilityBlock->value)->toBe('AvailabilityBlock');
});

it('can be created from string', function () {
    $type = RateType::from('Public');
    expect($type)->toBe(RateType::Public);
});

it('throws on invalid value', function () {
    RateType::from('InvalidType');
})->throws(\ValueError::class);
