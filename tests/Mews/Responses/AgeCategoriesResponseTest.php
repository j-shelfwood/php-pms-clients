<?php

use Shelfwood\PhpPms\Mews\Responses\AgeCategoriesResponse;

it('maps age categories response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/agecategories-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = AgeCategoriesResponse::map($mockData);

    expect($response->items)->toHaveCount(2)
        ->and($response->items[0]->classification)->toBe('Adult')
        ->and($response->cursor)->toBeNull();
});

it('handles empty age categories', function () {
    $response = AgeCategoriesResponse::map(['AgeCategories' => []]);

    expect($response->items)->toBeEmpty();
});
