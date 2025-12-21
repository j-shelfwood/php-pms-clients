<?php

use Shelfwood\PhpPms\Mews\Responses\ServicesResponse;

it('maps services response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/services-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ServicesResponse::map($mockData);

    expect($response->items)->toBeArray()
        ->and(count($response->items))->toBeGreaterThan(0)
        ->and($response->items[0]->id)->toBe('98a8bc9e-7b0e-4b9d-af1c-516fc60bf038')
        ->and($response->items[0]->names)->toBeArray()
        ->and($response->cursor)->toBeNull();
});

it('handles empty services', function () {
    $response = ServicesResponse::map(['Services' => []]);

    expect($response->items)->toBeEmpty();
});
