<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\CreateReservationPayload;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

it('creates payload with required fields', function () {
    $payload = new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 14:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 10:00:00 UTC'),
        personCounts: [
            ['AgeCategoryId' => 'adult-category', 'Count' => 2]
        ]
    );

    expect($payload->serviceId)->toBe('service-123')
        ->and($payload->customerId)->toBe('customer-456')
        ->and($payload->rateId)->toBe('rate-789')
        ->and($payload->personCounts)->toBe([['AgeCategoryId' => 'adult-category', 'Count' => 2]])
        ->and($payload->state)->toBe(ReservationState::Confirmed); // Default value
});

it('converts to array with all fields', function () {
    $payload = new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 14:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 10:00:00 UTC'),
        personCounts: [
            ['AgeCategoryId' => 'adult-category', 'Count' => 2],
            ['AgeCategoryId' => 'child-category', 'Count' => 1]
        ],
        requestedCategoryId: 'category-123',
        state: ReservationState::Confirmed,
        notes: 'Late check-in requested'
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['CustomerId', 'RateId', 'StartUtc', 'EndUtc', 'PersonCounts'])
        ->and($array['CustomerId'])->toBe('customer-456')
        ->and($array['RateId'])->toBe('rate-789')
        ->and($array['StartUtc'])->toMatch('/2025-01-15T14:00:00/')
        ->and($array['EndUtc'])->toMatch('/2025-01-18T10:00:00/')
        ->and($array['PersonCounts'])->toHaveCount(2)
        ->and($array['RequestedCategoryId'])->toBe('category-123')
        ->and($array['State'])->toBe('Confirmed')
        ->and($array['Notes'])->toBe('Late check-in requested');
});

it('filters null values from array output', function () {
    $payload = new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]]
    );

    $array = $payload->toArray();

    expect($array)->not->toHaveKey('RequestedCategoryId')
        ->and($array)->not->toHaveKey('Notes')
        ->and($array)->not->toHaveKey('ReleasedUtc');
});

it('includes releaseUtc for optional reservations', function () {
    $payload = new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]],
        state: ReservationState::Optional,
        releaseUtc: Carbon::parse('2025-01-10 12:00:00 UTC')
    );

    $array = $payload->toArray();

    expect($array['State'])->toBe('Optional')
        ->and($array)->toHaveKey('ReleasedUtc')
        ->and($array['ReleasedUtc'])->toMatch('/2025-01-10T12:00:00/');
});

it('throws exception when personCounts is empty', function () {
    new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        personCounts: []
    );
})->throws(\InvalidArgumentException::class, 'PersonCounts cannot be empty');

it('throws exception when start date is after end date', function () {
    new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]]
    );
})->throws(\InvalidArgumentException::class, 'StartUtc must be before EndUtc');

it('throws exception when start and end dates are equal', function () {
    $sameDate = Carbon::parse('2025-01-15 00:00:00 UTC');

    new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: $sameDate,
        endUtc: $sameDate,
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]]
    );
})->throws(\InvalidArgumentException::class, 'StartUtc must be before EndUtc');

it('throws exception when optional state missing releaseUtc', function () {
    new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]],
        state: ReservationState::Optional
    );
})->throws(\InvalidArgumentException::class, 'ReleaseUtc required for Optional reservations');

it('validates confirmed state does not require releaseUtc', function () {
    $payload = new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse('2025-01-15 00:00:00 UTC'),
        endUtc: Carbon::parse('2025-01-18 00:00:00 UTC'),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]],
        state: ReservationState::Confirmed
    );

    expect($payload->state)->toBe(ReservationState::Confirmed)
        ->and($payload->releaseUtc)->toBeNull();
});
