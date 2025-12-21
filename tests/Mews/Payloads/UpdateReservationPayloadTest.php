<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\UpdateReservationPayload;

it('creates minimal update payload with reservation ID only', function () {
    $payload = new UpdateReservationPayload(
        reservationId: 'reservation-123'
    );

    expect($payload->reservationId)->toBe('reservation-123');
});

it('converts to array with only non-null values', function () {
    $payload = new UpdateReservationPayload(
        reservationId: 'reservation-123',
        notes: 'Updated notes'
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['ReservationId', 'Notes'])
        ->and($array)->not->toHaveKey('StartUtc')
        ->and($array)->not->toHaveKey('EndUtc')
        ->and($array['Notes'])->toBe('Updated notes');
});

it('updates reservation dates', function () {
    $payload = new UpdateReservationPayload(
        reservationId: 'reservation-123',
        startUtc: Carbon::parse('2025-01-20'),
        endUtc: Carbon::parse('2025-01-25')
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['ReservationId', 'StartUtc', 'EndUtc'])
        ->and($array['StartUtc'])->toMatch('/2025-01-20/')
        ->and($array['EndUtc'])->toMatch('/2025-01-25/');
});

it('updates reservation state', function () {
    $payload = new UpdateReservationPayload(
        reservationId: 'reservation-123',
        state: 'Canceled'
    );

    expect($payload->toArray()['State'])->toBe('Canceled');
});

it('throws exception when reservation ID is empty', function () {
    new UpdateReservationPayload(reservationId: '');
})->throws(\InvalidArgumentException::class, 'ReservationId is required');

it('throws exception when start date is after end date', function () {
    new UpdateReservationPayload(
        reservationId: 'reservation-123',
        startUtc: Carbon::parse('2025-01-25'),
        endUtc: Carbon::parse('2025-01-20')
    );
})->throws(\InvalidArgumentException::class, 'StartUtc must be before EndUtc');

it('throws exception when optional state missing releaseUtc', function () {
    new UpdateReservationPayload(
        reservationId: 'reservation-123',
        state: 'Optional'
    );
})->throws(\InvalidArgumentException::class, 'ReleaseUtc required for Optional state');

it('allows optional state with releaseUtc', function () {
    $payload = new UpdateReservationPayload(
        reservationId: 'reservation-123',
        state: 'Optional',
        releaseUtc: Carbon::parse('2025-01-10')
    );

    expect($payload->toArray())->toHaveKeys(['State', 'ReleaseUtc']);
});
