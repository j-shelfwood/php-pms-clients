<?php

use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

it('maps resource availability metrics from strings', function () {
    expect(ResourceAvailabilityMetricType::from('Occupied'))->toBe(ResourceAvailabilityMetricType::Occupied)
        ->and(ResourceAvailabilityMetricType::from('ActiveResources'))->toBe(ResourceAvailabilityMetricType::ActiveResources);
});

