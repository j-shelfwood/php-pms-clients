<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use GuzzleHttp\ClientInterface;
use Psr\Log\NullLogger;


describe('PropertiesEndpointTest', function () {
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
    
    test('BookingManagerAPI::properties returns PropertiesResponse with PropertyDetails objects', function () {
    
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/all-properties.xml');
    
        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mockStream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
    
        $response = $this->api->properties();
    
        expect($response)->toBeInstanceOf(PropertiesResponse::class);
        expect($response->properties)->toBeArray();
        foreach ($response->properties as $property) {
            expect($property)->toBeInstanceOf(PropertyDetails::class);
        }
    });
    
    });
