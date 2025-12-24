<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

it('maps reservation from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/reservations-add.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $reservationData = $mockData['Reservations'][0]['Reservation'];

    $reservation = Reservation::map($reservationData);

    expect($reservation->id)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea')
        ->and($reservation->number)->toBe('52')
        ->and($reservation->state)->toBe(ReservationState::Confirmed)
        ->and($reservation->personCounts)->toBeArray()
        ->and($reservation->adultCount)->toBe(2)
        ->and($reservation->childCount)->toBe(0)
        ->and($reservation->origin)->toBe('Connector')
        ->and($reservation->requestedCategoryId)->toBe('773d5e42-de1e-43a0-9ce6-c3e7511c1e0a')
        ->and($reservation->groupId)->toBeNull()
        ->and($reservation->channelNumber)->toBe('WEB_001');
});

it('throws exception on missing required field', function () {
    Reservation::map(['Id' => 'test-id']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

it('falls back to StartUtc/EndUtc when ScheduledStartUtc/ScheduledEndUtc missing', function () {
    $reservation = Reservation::map([
        'Id' => 'reservation-1',
        'ServiceId' => 'service-1',
        'AccountId' => 'account-1',
        'Number' => '1',
        'State' => 'Confirmed',
        'RateId' => 'rate-1',
        'StartUtc' => '2025-01-01T00:00:00Z',
        'EndUtc' => '2025-01-02T00:00:00Z',
    ]);

    expect($reservation->scheduledStartUtc)->toBe('2025-01-01T00:00:00Z')
        ->and($reservation->scheduledEndUtc)->toBe('2025-01-02T00:00:00Z');
});
