<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

it('creates payload with required fields', function () {
    $payload = new GetAvailabilityPayload(
        serviceId: 'service-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );

    expect($payload->serviceId)->toBe('service-123');
});

it('converts to array with ISO 8601 dates', function () {
    $payload = new GetAvailabilityPayload(
        serviceId: 'service-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01 00:00:00 UTC'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31 23:59:59 UTC')
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['ServiceId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc', 'Metrics'])
        ->and($array['ServiceId'])->toBe('service-123')
        ->and($array['FirstTimeUnitStartUtc'])->toMatch('/2025-01-01T00:00:00/')
        ->and($array['LastTimeUnitStartUtc'])->toMatch('/2025-01-31T23:59:59/')
        ->and($array['Metrics'])->toContain(ResourceAvailabilityMetricType::Occupied->value);
});

it('throws exception when serviceId is empty', function () {
    new GetAvailabilityPayload(
        serviceId: '',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );
})->throws(\InvalidArgumentException::class, 'ServiceId is required');

it('throws exception when first date is after last date', function () {
    new GetAvailabilityPayload(
        serviceId: 'service-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-31'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-01')
    );
})->throws(\InvalidArgumentException::class, 'FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');

it('throws exception when dates are equal', function () {
    $sameDate = Carbon::parse('2025-01-15');

    new GetAvailabilityPayload(
        serviceId: 'service-123',
        firstTimeUnitStartUtc: $sameDate,
        lastTimeUnitStartUtc: $sameDate
    );
})->throws(\InvalidArgumentException::class, 'FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');

it('throws exception when metrics list is empty', function () {
    new GetAvailabilityPayload(
        serviceId: 'service-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01 00:00:00 UTC'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-02 00:00:00 UTC'),
        metrics: []
    );
})->throws(\InvalidArgumentException::class, 'Metrics cannot be empty');
