<?php

use Shelfwood\PhpPms\Mews\Concerns\ExtractsReservationDates;

// Test class that uses the trait
class ReservationDateExtractor
{
    use ExtractsReservationDates;

    public function getStartDate(array $reservation): ?string
    {
        return $this->extractReservationStartDate($reservation);
    }

    public function getEndDate(array $reservation): ?string
    {
        return $this->extractReservationEndDate($reservation);
    }

    public function getDates(array $reservation): array
    {
        return $this->extractReservationDates($reservation);
    }
}

beforeEach(function () {
    $this->extractor = new ReservationDateExtractor();
});

it('extracts modern ScheduledStartUtc field', function () {
    $reservation = [
        'ScheduledStartUtc' => '2025-01-15T14:00:00Z',
        'StartUtc' => '2025-01-15T12:00:00Z', // Legacy field should be ignored
    ];

    $startDate = $this->extractor->getStartDate($reservation);

    expect($startDate)->toBe('2025-01-15T14:00:00Z');
});

it('falls back to deprecated StartUtc when ScheduledStartUtc missing', function () {
    $reservation = [
        'StartUtc' => '2025-01-15T12:00:00Z',
    ];

    $startDate = $this->extractor->getStartDate($reservation);

    expect($startDate)->toBe('2025-01-15T12:00:00Z');
});

it('returns null when no start date fields present', function () {
    $reservation = [];

    $startDate = $this->extractor->getStartDate($reservation);

    expect($startDate)->toBeNull();
});

it('extracts modern ScheduledEndUtc field', function () {
    $reservation = [
        'ScheduledEndUtc' => '2025-01-18T10:00:00Z',
        'EndUtc' => '2025-01-18T12:00:00Z', // Legacy field should be ignored
    ];

    $endDate = $this->extractor->getEndDate($reservation);

    expect($endDate)->toBe('2025-01-18T10:00:00Z');
});

it('falls back to deprecated EndUtc when ScheduledEndUtc missing', function () {
    $reservation = [
        'EndUtc' => '2025-01-18T12:00:00Z',
    ];

    $endDate = $this->extractor->getEndDate($reservation);

    expect($endDate)->toBe('2025-01-18T12:00:00Z');
});

it('returns null when no end date fields present', function () {
    $reservation = [];

    $endDate = $this->extractor->getEndDate($reservation);

    expect($endDate)->toBeNull();
});

it('extracts both dates using modern fields', function () {
    $reservation = [
        'ScheduledStartUtc' => '2025-01-15T14:00:00Z',
        'ScheduledEndUtc' => '2025-01-18T10:00:00Z',
    ];

    $dates = $this->extractor->getDates($reservation);

    expect($dates)->toBe([
        'start' => '2025-01-15T14:00:00Z',
        'end' => '2025-01-18T10:00:00Z',
    ]);
});

it('extracts both dates using legacy fields', function () {
    $reservation = [
        'StartUtc' => '2025-01-15T12:00:00Z',
        'EndUtc' => '2025-01-18T12:00:00Z',
    ];

    $dates = $this->extractor->getDates($reservation);

    expect($dates)->toBe([
        'start' => '2025-01-15T12:00:00Z',
        'end' => '2025-01-18T12:00:00Z',
    ]);
});

it('extracts both dates with mixed modern and legacy fields', function () {
    $reservation = [
        'ScheduledStartUtc' => '2025-01-15T14:00:00Z',
        'EndUtc' => '2025-01-18T12:00:00Z',
    ];

    $dates = $this->extractor->getDates($reservation);

    expect($dates)->toBe([
        'start' => '2025-01-15T14:00:00Z',
        'end' => '2025-01-18T12:00:00Z',
    ]);
});

it('handles completely empty reservation object', function () {
    $reservation = [];

    $dates = $this->extractor->getDates($reservation);

    expect($dates)->toBe([
        'start' => null,
        'end' => null,
    ]);
});
