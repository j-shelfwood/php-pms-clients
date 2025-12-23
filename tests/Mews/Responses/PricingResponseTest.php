<?php

use Shelfwood\PhpPms\Mews\Responses\PricingResponse;

it('maps pricing response from API with adjustments', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/rates-getpricing.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = PricingResponse::map($mockData);

    expect($response->currency)->toBe('GBP')
        ->and($response->timeUnitStartsUtc)->toBeArray()
        ->and($response->categoryPrices)->toBeArray()
        ->and($response->categoryAdjustments)->toBeArray()
        ->and($response->ageCategoryAdjustments)->toBeArray()
        ->and($response->relativeAdjustment)->toBe(0.0)
        ->and($response->absoluteAdjustment)->toBe(0.0)
        ->and($response->emptyUnitAdjustment)->toBe(0.0)
        ->and($response->extraUnitAdjustment)->toBe(0.0);
});
