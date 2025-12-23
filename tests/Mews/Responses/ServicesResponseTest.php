<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ServicesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;

it('maps services response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/services-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ServicesResponse::map($mockData);

    expect($response->items)->toBeInstanceOf(Collection::class)
        ->and($response->items->count())->toBeGreaterThan(0)
        ->and($response->items[0])->toBeInstanceOf(Service::class)
        ->and($response->items[0]->id)->toBe('98a8bc9e-7b0e-4b9d-af1c-516fc60bf038')
        ->and($response->items[0]->names)->toBeArray()
        ->and($response->cursor)->toBeNull();
});

it('handles empty services', function () {
    $response = ServicesResponse::map(['Services' => []]);

    expect($response->items)->toBeEmpty();
});
