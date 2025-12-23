<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ReservationsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;

it('maps reservations response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/reservations-add.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ReservationsResponse::map($mockData);

    expect($response->items)->toBeInstanceOf(Collection::class)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0])->toBeInstanceOf(Reservation::class)
        ->and($response->items[0]->number)->toBe('52')
        ->and($response->cursor)->toBeNull();
});

it('handles empty reservations', function () {
    $response = ReservationsResponse::map(['Reservations' => []]);

    expect($response->items)->toBeEmpty();
});
