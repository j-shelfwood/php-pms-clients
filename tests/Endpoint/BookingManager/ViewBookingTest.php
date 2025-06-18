<?php

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use GuzzleHttp\ClientInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\Helpers\TestHelpers;

// Import the Golden Master assertion function
use function Tests\Helpers\assertViewBookingDetailsMatchesExpected;

describe('ViewBookingTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: viewBooking correctly maps all fields from rich response', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/view-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->viewBooking(16);

        expect($response)->not()->toBeNull();

        // Golden Master validation - validates ALL fields
        assertViewBookingDetailsMatchesExpected($response->booking);
    });
});
