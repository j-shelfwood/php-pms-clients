<?php

use Shelfwood\PhpPms\Mews\Enums\ServiceType;

it('has all service types', function () {
    expect(ServiceType::cases())->toHaveCount(2)
        ->and(ServiceType::Bookable->value)->toBe('Bookable')
        ->and(ServiceType::Additional->value)->toBe('Additional');
});

it('can be created from string', function () {
    $type = ServiceType::from('Bookable');
    expect($type)->toBe(ServiceType::Bookable);
});

it('throws on invalid value', function () {
    ServiceType::from('InvalidType');
})->throws(\ValueError::class);
