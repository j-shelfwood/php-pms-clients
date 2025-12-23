<?php

use Shelfwood\PhpPms\Mews\Enums\ServiceType;

it('has all service types', function () {
    expect(ServiceType::cases())->toHaveCount(5)
        ->and(ServiceType::Accommod->value)->toBe('Accommod')
        ->and(ServiceType::Additional->value)->toBe('Additional')
        ->and(ServiceType::Bookable->value)->toBe('Bookable')
        ->and(ServiceType::Orderable->value)->toBe('Orderable')
        ->and(ServiceType::Reservable->value)->toBe('Reservable');
});

it('can be created from string', function () {
    $type = ServiceType::from('Bookable');
    expect($type)->toBe(ServiceType::Bookable);
});

it('throws on invalid value', function () {
    ServiceType::from('InvalidType');
})->throws(\ValueError::class);
