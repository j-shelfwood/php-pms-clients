<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


describe('PropertyEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'dummy-username',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('BookingManagerAPI::property returns PropertyResponse with populated PropertyDetails', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/property-by-id.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(21663);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);
        expect($response->property->external_id)->toBe(21663);
        expect($response->property->name)->toBe('Runstraat suite Amsterdam');
        expect($response->property->identifier)->toBe('#487');
        expect($response->property->status)->toBe('live');
    });
    test('BookingManagerAPI::property throws HttpClientException on generic API error', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/generic-error.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $this->api->property(1);
    })->throws(\Shelfwood\PhpPms\Exceptions\HttpClientException::class);

    });
