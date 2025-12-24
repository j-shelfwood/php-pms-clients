<?php

use Shelfwood\PhpPms\Mews\Responses\CalendarResponse;

it('maps calendar response from availability and pricing data', function () {
    $availabilityPath = __DIR__ . '/../../../mocks/mews/responses/services-getavailability.json';
    $pricingPath = __DIR__ . '/../../../mocks/mews/responses/rates-getpricing.json';

    $availabilityData = json_decode(file_get_contents($availabilityPath), true);
    $pricingData = json_decode(file_get_contents($pricingPath), true);

    $response = CalendarResponse::map($availabilityData, $pricingData);

    expect($response->availability)->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse::class)
        ->and($response->pricing)->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\PricingResponse::class)
        ->and($response->availability->resourceCategoryAvailabilities)->toHaveCount(4)
        ->and($response->pricing->currency)->toBe('GBP');
});
