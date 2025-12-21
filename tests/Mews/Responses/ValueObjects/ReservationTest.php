<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;

it('maps reservation from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/reservations-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $reservationData = $mockData['Reservations'][0];

    $reservation = Reservation::map($reservationData);

    expect($reservation->id)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea')
        ->and($reservation->number)->toBe('52')
        ->and($reservation->state)->toBe('Confirmed')
        ->and($reservation->personCounts)->toBeArray();
});

it('throws exception on missing required field', function () {
    Reservation::map(['Id' => 'test-id']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);
