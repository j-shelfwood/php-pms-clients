<?php

use Shelfwood\PhpPms\Mews\Responses\RatesResponse;

it('maps rates response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/rates-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = RatesResponse::map($mockData);

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]->type)->toBe('Public')
        ->and($response->rateGroups)->toBeArray()
        ->and($response->cursor)->toBeNull();
});

it('handles empty rates', function () {
    $response = RatesResponse::map(['Rates' => []]);

    expect($response->items)->toBeEmpty();
});
