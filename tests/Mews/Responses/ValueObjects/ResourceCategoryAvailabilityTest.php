<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

it('maps resource category availability with metrics', function () {
    $availability = ResourceCategoryAvailability::map([
        'ResourceCategoryId' => 'category-1',
        'Metrics' => [
            ResourceAvailabilityMetricType::Occupied->value => [1, 2],
            ResourceAvailabilityMetricType::ActiveResources->value => [3, 3],
        ],
    ]);

    expect($availability->resourceCategoryId)->toBe('category-1')
        ->and($availability->metrics)->toHaveKey(ResourceAvailabilityMetricType::Occupied->value);
});

it('throws on missing ResourceCategoryId', function () {
    ResourceCategoryAvailability::map(['Metrics' => []]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

