<?php

use Shelfwood\PhpPms\Mews\Responses\ResourceCategoriesResponse;

it('maps resource categories response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/resourcecategories-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ResourceCategoriesResponse::map($mockData);

    expect($response->items)->toHaveCount(4)
        ->and($response->items[0]->type)->toBe('Room')
        ->and($response->cursor)->toBeNull();
});

it('handles empty resource categories', function () {
    $response = ResourceCategoriesResponse::map(['ResourceCategories' => []]);

    expect($response->items)->toBeEmpty();
});
