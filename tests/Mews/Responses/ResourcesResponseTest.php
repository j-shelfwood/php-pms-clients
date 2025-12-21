<?php

use Shelfwood\PhpPms\Mews\Responses\ResourcesResponse;

it('maps resources response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/resources-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ResourcesResponse::map($mockData);

    expect($response->items)->toHaveCount(10)
        ->and($response->items[0]->name)->toBe('Updated Updated Updated Updated Updated')
        ->and($response->cursor)->toBe('9868b6d9-1e6d-4e85-a64a-b731628a0da2');
});

it('handles empty resources', function () {
    $response = ResourcesResponse::map(['Resources' => []]);

    expect($response->items)->toBeEmpty();
});
