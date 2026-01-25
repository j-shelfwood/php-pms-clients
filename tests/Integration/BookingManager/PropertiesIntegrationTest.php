<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use GuzzleHttp\Client;
use Psr\Log\NullLogger;

/**
 * Integration Tests for BookingManager Properties Endpoint
 *
 * Validates real API responses match expected structure and mock expectations.
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
});

describe('Properties Integration', function () {
    test('real API returns valid PropertiesResponse', function () {
        $response = $this->api->properties();

        expect($response)->toBeInstanceOf(PropertiesResponse::class)
            ->and($response->properties)->toBeInstanceOf(\Illuminate\Support\Collection::class);

        // If properties exist, validate structure
        if ($response->properties->isNotEmpty()) {
            foreach ($response->properties as $property) {
                expect($property)->toBeInstanceOf(PropertyDetails::class)
                    ->and($property->external_id)->toBeInt()->toBeGreaterThan(0)
                    ->and($property->name)->toBeString()->not->toBeEmpty()
                    ->and($property->max_persons)->toBeInt()->toBeGreaterThanOrEqualTo(0)
                    ->and($property->minimal_nights)->toBeInt()->toBeGreaterThanOrEqualTo(0);
            }
        }
    });

    test('real API property details have required nested objects', function () {
        $response = $this->api->properties();

        if ($response->properties->isEmpty()) {
            $this->markTestSkipped('No properties available in API response');
        }

        $firstProperty = $response->properties->first();

        expect($firstProperty->provider)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyProvider::class)
            ->and($firstProperty->location)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyLocation::class)
            ->and($firstProperty->supplies)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertySupplies::class)
            ->and($firstProperty->service)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyService::class)
            ->and($firstProperty->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class)
            ->and($firstProperty->content)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyContent::class);
    });

    test('real API property IDs are unique', function () {
        $response = $this->api->properties();

        if ($response->properties->isEmpty()) {
            $this->markTestSkipped('No properties available in API response');
        }

        $propertyIds = $response->properties->pluck('external_id');
        $uniqueIds = $propertyIds->unique();

        expect($uniqueIds->count())->toBe($propertyIds->count());
    });

    test('real API properties have valid status enums', function () {
        $response = $this->api->properties();

        if ($response->properties->isEmpty()) {
            $this->markTestSkipped('No properties available in API response');
        }

        foreach ($response->properties as $property) {
            if ($property->status !== null) {
                expect($property->status)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus::class);
            }
        }
    });

    test('real API response can be filtered and mapped', function () {
        $response = $this->api->properties();

        if ($response->properties->isEmpty()) {
            $this->markTestSkipped('No properties available in API response');
        }

        // Test collection operations
        $activeProperties = $response->properties->filter(function ($property) {
            return $property->status === \Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus::Active;
        });

        expect($activeProperties)->toBeInstanceOf(\Illuminate\Support\Collection::class);

        // Test mapping
        $propertyNames = $response->properties->pluck('name');
        expect($propertyNames)->toBeInstanceOf(\Illuminate\Support\Collection::class);
        expect($propertyNames->every(fn($name) => is_string($name)))->toBeTrue();
    });

    test('real API properties have valid cost data types', function () {
        $response = $this->api->properties();

        if ($response->properties->isEmpty()) {
            $this->markTestSkipped('No properties available in API response');
        }

        $firstProperty = $response->properties->first();

        expect($firstProperty->cleaning_costs)->toBeFloat()
            ->and($firstProperty->deposit_costs)->toBeFloat();

        if ($firstProperty->prepayment !== null) {
            expect($firstProperty->prepayment)->toBeFloat();
        }

        if ($firstProperty->fee !== null) {
            expect($firstProperty->fee)->toBeFloat();
        }
    });

    test('real API response structure matches mock file', function () {
        $response = $this->api->properties();

        // Load mock response for structural comparison
        $mockXml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/all-properties.xml');
        $mockData = \Shelfwood\PhpPms\Http\XMLParser::parse($mockXml);
        $mockResponse = PropertiesResponse::map($mockData);

        // Validate both responses have same object structure
        expect($response)->toBeInstanceOf(get_class($mockResponse))
            ->and($response->properties)->toBeInstanceOf(get_class($mockResponse->properties));

        // If real response has properties, compare first item structure
        if ($response->properties->isNotEmpty() && $mockResponse->properties->isNotEmpty()) {
            $realProperty = $response->properties->first();
            $mockProperty = $mockResponse->properties->first();

            // Same class
            expect(get_class($realProperty))->toBe(get_class($mockProperty));

            // Same public properties
            $realProps = array_keys(get_object_vars($realProperty));
            $mockProps = array_keys(get_object_vars($mockProperty));

            expect(count(array_diff($realProps, $mockProps)))->toBe(0)
                ->and(count(array_diff($mockProps, $realProps)))->toBe(0);
        }
    });
})->group('integration', 'bookingmanager', 'slow');
