<?php

use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;

it('maps availability response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/services-getavailability.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = AvailabilityResponse::map($mockData);

    expect($response->timeUnitStartsUtc)->toHaveCount(5)
        ->and($response->categoryAvailabilities)->toHaveCount(1)
        ->and($response->categoryAvailabilities[0]->categoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349');
});

it('handles empty availability', function () {
    $response = AvailabilityResponse::map([]);

    expect($response->timeUnitStartsUtc)->toBeEmpty()
        ->and($response->categoryAvailabilities)->toBeEmpty();
});
