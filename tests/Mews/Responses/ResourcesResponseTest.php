<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ResourcesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;

it('maps resources response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/resources-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ResourcesResponse::map($mockData);

    expect($response->items)->toBeInstanceOf(Collection::class)
        ->and($response->items->count())->toBeGreaterThan(0)
        ->and($response->items[0])->toBeInstanceOf(Resource::class)
        ->and($response->items[0]->name)->toBe('0. Sirius ')
        ->and($response->cursor)->toBeNull();
});

it('handles empty resources', function () {
    $response = ResourcesResponse::map(['Resources' => []]);

    expect($response->items)->toBeEmpty();
});
