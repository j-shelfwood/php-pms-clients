<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\FinalizeBookingResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


describe('FinalizeBookingEndpointTest', function () {
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
    
    test('BookingManagerAPI::finalizeBooking returns FinalizeBookingResponse with id and message', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/create-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
    
        $response = $this->api->finalizeBooking(171830);
    
        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->bookingId)->toBe(171830);
        expect($response->identifier)->toBe('BILL-171830-148-AMS-21663-2024-02-08');
    });
    
    });
