<?php

use Shelfwood\PhpPms\Mews\Responses\PricingResponse;

it('maps pricing response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/rates-getpricing.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = PricingResponse::map($mockData);

    expect($response->currency)->toBe('EUR')
        ->and($response->timeUnitStartsUtc)->toHaveCount(3)
        ->and($response->baseAmountPrices)->toHaveCount(3)
        ->and($response->categoryPrices)->toHaveCount(1);
});

it('handles empty pricing', function () {
    $response = PricingResponse::map([]);

    expect($response->currency)->toBe('EUR')
        ->and($response->timeUnitStartsUtc)->toBeEmpty()
        ->and($response->baseAmountPrices)->toBeEmpty()
        ->and($response->categoryPrices)->toBeEmpty();
});
