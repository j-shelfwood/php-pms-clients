<?php

use Shelfwood\PhpPms\Mews\Enums\ResourceState;

it('has all resource states', function () {
    expect(ResourceState::cases())->toHaveCount(5)
        ->and(ResourceState::Dirty->value)->toBe('Dirty')
        ->and(ResourceState::Clean->value)->toBe('Clean')
        ->and(ResourceState::Inspected->value)->toBe('Inspected')
        ->and(ResourceState::OutOfService->value)->toBe('OutOfService')
        ->and(ResourceState::OutOfOrder->value)->toBe('OutOfOrder');
});

it('can be created from string', function () {
    $state = ResourceState::from('Clean');
    expect($state)->toBe(ResourceState::Clean);
});

it('throws on invalid value', function () {
    ResourceState::from('InvalidState');
})->throws(\ValueError::class);
