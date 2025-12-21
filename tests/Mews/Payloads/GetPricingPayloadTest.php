<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\GetPricingPayload;

it('creates payload with required fields', function () {
    $payload = new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );

    expect($payload->rateId)->toBe('rate-123');
});

it('converts to array with ISO 8601 dates', function () {
    $payload = new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-15 14:00:00'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-18 10:00:00')
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['RateId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc'])
        ->and($array['RateId'])->toBe('rate-123')
        ->and($array['FirstTimeUnitStartUtc'])->toMatch('/2025-01-15T14:00:00/')
        ->and($array['LastTimeUnitStartUtc'])->toMatch('/2025-01-18T10:00:00/');
});

it('includes occupancy configuration when provided', function () {
    $payload = new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31'),
        occupancyConfiguration: [
            'AdultCount' => 2,
            'ChildCount' => 1
        ]
    );

    $array = $payload->toArray();

    expect($array)->toHaveKey('OccupancyConfiguration')
        ->and($array['OccupancyConfiguration'])->toBe(['AdultCount' => 2, 'ChildCount' => 1]);
});

it('filters null occupancy configuration', function () {
    $payload = new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );

    $array = $payload->toArray();

    expect($array)->not->toHaveKey('OccupancyConfiguration');
});

it('throws exception when rateId is empty', function () {
    new GetPricingPayload(
        rateId: '',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );
})->throws(\InvalidArgumentException::class, 'RateId is required');

it('throws exception when first date is after last date', function () {
    new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-31'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-01')
    );
})->throws(\InvalidArgumentException::class, 'FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');

it('throws exception when dates are equal', function () {
    $sameDate = Carbon::parse('2025-01-15');

    new GetPricingPayload(
        rateId: 'rate-123',
        firstTimeUnitStartUtc: $sameDate,
        lastTimeUnitStartUtc: $sameDate
    );
})->throws(\InvalidArgumentException::class, 'FirstTimeUnitStartUtc must be before LastTimeUnitStartUtc');
