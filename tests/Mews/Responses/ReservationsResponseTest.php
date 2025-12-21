<?php

use Shelfwood\PhpPms\Mews\Responses\ReservationsResponse;

it('maps reservations response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/reservations-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ReservationsResponse::map($mockData);

    expect($response->items)->toHaveCount(2)
        ->and($response->items[0]->number)->toBe('52')
        ->and($response->cursor)->toBeNull();
});

it('handles empty reservations', function () {
    $response = ReservationsResponse::map(['Reservations' => []]);

    expect($response->items)->toBeEmpty();
});
