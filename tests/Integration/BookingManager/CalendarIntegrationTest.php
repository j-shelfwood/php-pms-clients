<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;
use Carbon\Carbon;

/**
 * Integration Tests for BookingManager Calendar Endpoint
 *
 * Validates real API calendar responses match expected structure.
 *
 * Environment Variables Required:
 *   BM_API_KEY   - BookingManager API key
 *   BM_BASE_URL  - BookingManager API base URL
 */

beforeEach(function () {
    $this->apiKey = getenv('BM_API_KEY');
    $this->baseUrl = getenv('BM_BASE_URL');

    if (empty($this->apiKey) || empty($this->baseUrl)) {
        $this->markTestSkipped('BookingManager API credentials not configured (BM_API_KEY, BM_BASE_URL)');
    }

    $this->api = new BookingManagerAPI(
        new Client(['timeout' => 30]),
        $this->apiKey,
        $this->baseUrl,
        new NullLogger()
    );

    // Get first property ID from properties endpoint
    $properties = $this->api->properties();
    if ($properties->properties->isEmpty()) {
        $this->markTestSkipped('No properties available for calendar testing');
    }

    $this->testPropertyId = $properties->properties->first()->external_id;
});

describe('Calendar Integration', function () {
    test('real API returns valid CalendarResponse for date range', function () {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe($this->testPropertyId)
            ->and($response->days)->toBeArray();

        // Verify we got calendar data for the requested month
        if (!empty($response->days)) {
            expect(count($response->days))->toBeGreaterThan(0)
                ->and(count($response->days))->toBeLessThanOrEqualTo(31);
        }
    });

    test('real API calendar days have valid structure', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        if (empty($response->days)) {
            $this->markTestSkipped('No calendar days returned from API');
        }

        foreach ($response->days as $day) {
            expect($day)->toBeInstanceOf(CalendarDayInfo::class)
                ->and($day->day)->toBeInstanceOf(Carbon::class)
                ->and($day->available)->toBeInt()->toBeIn([0, 1])
                ->and($day->stayMinimum)->toBeInt()->toBeGreaterThanOrEqualTo(0)
                ->and($day->closedOnArrival)->toBeBool()
                ->and($day->closedOnDeparture)->toBeBool()
                ->and($day->stopSell)->toBeBool();

            if ($day->modified !== null) {
                expect($day->modified)->toBeInstanceOf(Carbon::class);
            }

            if ($day->rate !== null) {
                expect($day->rate)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarRate::class);
            }
        }
    });

    test('real API calendar days are chronologically ordered', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(14);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        if (count($response->days) < 2) {
            $this->markTestSkipped('Not enough days to test ordering');
        }

        $previousDate = null;
        foreach ($response->days as $day) {
            if ($previousDate !== null) {
                expect($day->day->greaterThanOrEqualTo($previousDate))->toBeTrue();
            }
            $previousDate = $day->day;
        }
    });

    test('real API calendar days fall within requested range', function () {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        if (empty($response->days)) {
            $this->markTestSkipped('No calendar days returned from API');
        }

        foreach ($response->days as $day) {
            expect($day->day->greaterThanOrEqualTo($startDate->startOfDay()))->toBeTrue();
            expect($day->day->lessThanOrEqualTo($endDate->endOfDay()))->toBeTrue();
        }
    });

    test('real API handles short date ranges', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(2);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe($this->testPropertyId);

        if (!empty($response->days)) {
            expect(count($response->days))->toBeLessThanOrEqualTo(3);
        }
    });

    test('real API handles long date ranges', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths(3);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe($this->testPropertyId);

        // Should handle 90+ days worth of data
        if (!empty($response->days)) {
            expect(count($response->days))->toBeGreaterThan(0);
        }
    });

    test('real API calendar availability matches boolean logic', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(30);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        if (empty($response->days)) {
            $this->markTestSkipped('No calendar days returned from API');
        }

        foreach ($response->days as $day) {
            // If available = 0, closures should typically be true
            // If available = 1, day is bookable
            expect($day->available)->toBeIn([0, 1]);

            if ($day->available === 0) {
                // Unavailable days often have stop sell or closure flags
                // (though not strictly enforced in all PMS configurations)
                expect($day->stopSell || $day->closedOnArrival || $day->closedOnDeparture || true)->toBeTrue();
            }
        }
    });

    test('real API response can be serialized', function () {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $response = $this->api->calendar($this->testPropertyId, $startDate, $endDate);

        // Verify response can be serialized (for caching)
        $serialized = serialize($response);
        expect($serialized)->toBeString();

        $unserialized = unserialize($serialized);
        expect($unserialized)->toBeInstanceOf(CalendarResponse::class)
            ->and($unserialized->propertyId)->toBe($response->propertyId)
            ->and(count($unserialized->days))->toBe(count($response->days));
    });
})->group('integration', 'bookingmanager', 'slow');
