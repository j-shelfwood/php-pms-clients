<?php

use Shelfwood\PhpPms\Mews\Enums\ReservationState;

it('has all reservation states', function () {
    expect(ReservationState::cases())->toHaveCount(7)
        ->and(ReservationState::Confirmed->value)->toBe('Confirmed')
        ->and(ReservationState::Optional->value)->toBe('Optional')
        ->and(ReservationState::Inquired->value)->toBe('Inquired')
        ->and(ReservationState::Started->value)->toBe('Started')
        ->and(ReservationState::Processed->value)->toBe('Processed')
        ->and(ReservationState::Canceled->value)->toBe('Canceled')
        ->and(ReservationState::Requested->value)->toBe('Requested');
});

it('can be created from string', function () {
    $state = ReservationState::from('Confirmed');
    expect($state)->toBe(ReservationState::Confirmed);
});

it('throws on invalid value', function () {
    ReservationState::from('InvalidState');
})->throws(\ValueError::class);
