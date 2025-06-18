<?php

declare(strict_types=1);

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Shelfwood\PhpPms\Exceptions\ApiException;
use Tests\Helpers\TestData;
use Tests\Helpers\TestHelpers;

// Import the Golden Master assertion functions
use function Tests\Helpers\assertPropertyDetailsMatchesExpected;
use function Tests\Helpers\assertPropertyDetails;

describe('PropertyEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: property correctly maps all fields from rich response', function () {
        $mockResponsePath = TestHelpers::getMockFilePath('property-by-id.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(21663);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);

        // Golden Master validation - validates ALL fields
        assertPropertyDetailsMatchesExpected($response->property);
    });

    test('it correctly maps a minimal property with many missing optional fields', function () {
        $mockResponsePath = TestHelpers::getMockFilePath('property-minimal.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(6743);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);

        // Verify that minimal property has expected null values for optional fields
        expect($response->property->external_id)->toBe(6743);
        expect($response->property->name)->toBe('Historical Heart apartment Amsterdam');
        expect($response->property->identifier)->toBe('BBA0052');
        expect($response->property->status->value)->toBe('live');

        // These should be null or default values for minimal property
        expect($response->property->view)->toBeNull();
        expect($response->property->internet)->toBeNull();
        expect($response->property->size)->toBeNull();
        expect($response->property->service->cleaning)->toBeFalse();
        expect($response->property->images)->toBeEmpty();
    });

    test('it correctly maps an inactive property', function () {
        $mockResponsePath = TestHelpers::getMockFilePath('property-inactive.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(6861);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);

        expect($response->property->external_id)->toBe(6861);
        expect($response->property->name)->toBe('Plantage Park suite Amsterdam');
        expect($response->property->identifier)->toBe('BBA0318');
        expect($response->property->status->value)->toBe('inactive');
    });

    test('it correctly maps a property with comprehensive data and images', function() {
        $mockResponsePath = TestHelpers::getMockFilePath('property-richest.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(22097);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);

        // Verify this is the richest property with all fields populated
        expect($response->property->external_id)->toBe(22097);
        expect($response->property->name)->toBe('Albert Cuyp III B studio Amsterdam');
        expect($response->property->identifier)->toBe('#622');
        expect($response->property->status->value)->toBe('live');

        // Verify rich fields are populated
        expect($response->property->view)->not->toBeNull();
        expect($response->property->internet)->not->toBeNull();
        expect($response->property->size)->toBe(45.0);
        expect($response->property->images)->toBeArray()->toHaveCount(3);
        expect($response->property->images[0])->toBeInstanceOf(Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyImage::class);

        // Test some specific rich values
        expect($response->property->view->value)->toBe('street');
        expect($response->property->internet->value)->toBe('wifi');
        expect($response->property->max_persons)->toBe(2);
        expect($response->property->fee)->toBe(10.0);
    });

    test('it correctly maps various property responses', function (string $mockFile, array $expectedData) {
        $xml = file_get_contents(TestHelpers::getMockFilePath($mockFile));
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property($expectedData['external_id']);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        assertPropertyDetails($response->property, $expectedData);
    })->with([
        'rich property' => ['property-richest.xml', TestData::getExpectedRichestPropertyData()],
        'inactive property' => ['property-inactive.xml', TestData::getExpectedInactivePropertyData()],
        'minimal property' => ['property-minimal.xml', TestData::getExpectedMinimalPropertyData()],
        'standard property by id' => ['property-by-id.xml', TestData::getExpectedPropertyData()],
    ]);

    test('BookingManagerAPI::property throws ApiException on API error', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('generic-error.xml'));
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        expect(fn() => $this->api->property(12345))
            ->toThrow(ApiException::class, 'Sample error');
    });
});
