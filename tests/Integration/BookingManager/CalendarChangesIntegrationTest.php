<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;
use Carbon\Carbon;

/**
 * Integration Tests for BookingManager Calendar Changes Endpoint
 *
 * These tests validate against the REAL BookingManager API to ensure:
 * 1. Mock files accurately reflect actual API responses
 * 2. Code handles real-world API scenarios
 * 3. API changes are detected before production deployment
 *
 * Environment Variables Required:
 *   BM_API_KEY   - BookingManager API key
 *   BM_BASE_URL  - BookingManager API base URL
 *
 * Skip Conditions:
 *   - Missing API credentials
 *   - CI environment without API access
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
});

describe('CalendarChanges Integration', function () {
    test('real API returns valid CalendarChangesResponse', function () {
        $since = Carbon::now()->subDays(7);
        $response = $this->api->calendarChanges($since);

        expect($response)->toBeInstanceOf(CalendarChangesResponse::class)
            ->and($response->changes)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($response->amount)->toBeInt()
            ->and($response->time)->toSatisfy(fn($t) => $t === null || $t instanceof Carbon);
    });

    test('real API response structure matches mock expectations', function () {
        $since = Carbon::now()->subDays(30);
        $response = $this->api->calendarChanges($since);

        // Validate structure matches what Golden Master tests expect
        expect($response)->toBeInstanceOf(CalendarChangesResponse::class);

        // If changes exist, validate structure
        if ($response->changes->isNotEmpty()) {
            $firstChange = $response->changes->first();

            expect($firstChange)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange::class)
                ->and($firstChange->propertyId)->toBeInt()
                ->and($firstChange->months)->toBeArray();

            // Verify amount matches count
            expect($response->amount)->toBe($response->changes->count());

            // Verify time is present when changes exist
            expect($response->time)->toBeInstanceOf(Carbon::class);
        } else {
            // Empty response validation
            expect($response->amount)->toBe(0)
                ->and($response->time)->toBeNull();
        }
    });

    test('real API handles very old timestamps', function () {
        // Request changes from 2 years ago - should return many changes or empty
        $since = Carbon::now()->subYears(2);
        $response = $this->api->calendarChanges($since);

        expect($response)->toBeInstanceOf(CalendarChangesResponse::class);

        // Should handle large datasets gracefully
        if ($response->changes->isNotEmpty()) {
            // Verify all property IDs are valid integers
            foreach ($response->changes as $change) {
                expect($change->propertyId)->toBeInt()->toBeGreaterThan(0);
            }

            // Verify no duplicate property IDs (unique constraint)
            $propertyIds = $response->changes->pluck('propertyId');
            expect($propertyIds->unique()->count())->toBe($propertyIds->count());
        }
    });

    test('real API handles recent timestamps', function () {
        // Request changes from last hour - might be empty
        $since = Carbon::now()->subHour();
        $response = $this->api->calendarChanges($since);

        expect($response)->toBeInstanceOf(CalendarChangesResponse::class);

        // Should return valid response even if no changes
        expect($response->changes)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($response->amount)->toBe($response->changes->count());
    });

    test('real API response can be serialized', function () {
        $since = Carbon::now()->subDays(7);
        $response = $this->api->calendarChanges($since);

        // Verify response can be serialized (for caching, logging)
        $serialized = serialize($response);
        expect($serialized)->toBeString();

        $unserialized = unserialize($serialized);
        expect($unserialized)->toBeInstanceOf(CalendarChangesResponse::class)
            ->and($unserialized->amount)->toBe($response->amount);
    });
})->group('integration', 'bookingmanager', 'slow');
