<?php

use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;

it('maps restrictions response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = RestrictionsResponse::map($mockData);

    expect($response->items)->toHaveCount(2)
        ->and($response->items[0]->type)->toBe('Stay')
        ->and($response->cursor)->toBeNull();
});

it('handles empty restrictions', function () {
    $response = RestrictionsResponse::map(['Restrictions' => []]);

    expect($response->items)->toBeEmpty();
});
