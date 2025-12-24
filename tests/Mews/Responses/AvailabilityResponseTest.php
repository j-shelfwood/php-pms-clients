<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;

it('maps availability response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/services-getavailability.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = AvailabilityResponse::map($mockData);

    expect($response->timeUnitStartsUtc)->toBeArray()
        ->and($response->timeUnitStartsUtc)->toHaveCount(6)
        ->and($response->resourceCategoryAvailabilities)->toBeInstanceOf(Collection::class)
        ->and($response->resourceCategoryAvailabilities)->toHaveCount(4)
        ->and($response->resourceCategoryAvailabilities[0])->toBeInstanceOf(ResourceCategoryAvailability::class)
        ->and($response->resourceCategoryAvailabilities[0]->resourceCategoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349');
});

it('handles empty availability', function () {
    $response = AvailabilityResponse::map([]);

    expect($response->timeUnitStartsUtc)->toBeEmpty()
        ->and($response->resourceCategoryAvailabilities)->toBeEmpty();
});
