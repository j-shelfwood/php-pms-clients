<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\Helpers\TestHelpers;

// Import the Golden Master assertion functions
use function Tests\Helpers\assertPropertyDetailsMatchesExpected;

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

    test('BookingManagerAPI::property throws ApiException on API error', function () {
        $mockResponsePath = Tests\Helpers\TestHelpers::getMockFilePath('generic-error.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $this->api->property(123);
    })->throws(\Shelfwood\PhpPms\Exceptions\ApiException::class);
});
