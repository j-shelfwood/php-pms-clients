<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;
use Shelfwood\PhpPms\Mews\Enums\ServiceType;

it('maps service from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/services-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $serviceData = $mockData['Services'][0];

    $service = Service::map($serviceData);

    expect($service->id)->toBe('98a8bc9e-7b0e-4b9d-af1c-516fc60bf038')
        ->and($service->isActive)->toBeFalse()
        ->and($service->names)->toHaveKey('en-US')
        ->and($service->type)->toBe(ServiceType::Orderable)
        ->and($service->name)->toBe('Eating at the Family')
        ->and($service->promotions)->toBeArray();
});

it('throws exception on missing required field', function () {
    Service::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);
