<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus; // Added import
use Shelfwood\PhpPms\BookingManager\Responses\CancelBookingResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


describe('CancelBookingEndpointTest', function () {
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

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with correct status on failure from mock', function () {
        // This mock represents a failed cancellation where the API itself didn't error,
        // but the cancellation was not successful (e.g. status "failed")
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/cancel-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->cancelBooking(171838, 'reason');

        expect($response)->toBeInstanceOf(CancelBookingResponse::class);
        // The mock has <status>failed</status>, which is not a direct case in BookingStatus enum.
        // The CancelBookingResponse::map logic currently defaults unmappable non-error statuses to BookingStatus::ERROR.
        // If "failed" should be a specific enum case, BookingStatus enum and map logic would need adjustment.
        // Based on current logic, this should be BookingStatus::ERROR.
        expect($response->status)->toBe(BookingStatus::ERROR);
        expect($response->message)->toBe('Other: '); // Message from the mock
    });

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with CANCELLED status on successful cancellation', function () {
        // Simulate a successful cancellation response XML
        $successfulXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<booking id="12345">
    <status>cancelled</status>
    <message>Booking has been successfully cancelled.</message>
</booking>
XML;
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($successfulXml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->cancelBooking(12345, 'User request');

        expect($response)->toBeInstanceOf(CancelBookingResponse::class);
        expect($response->status)->toBe(BookingStatus::CANCELLED);
        expect($response->message)->toBe('Booking has been successfully cancelled.');
    });

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with ERROR status on API error response', function () {
        // This mock represents an API error (e.g. <error> tag in response)
        $errorXml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/generic-error.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($errorXml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->cancelBooking(99999, 'some reason');

        expect($response)->toBeInstanceOf(CancelBookingResponse::class);
        expect($response->status)->toBe(BookingStatus::ERROR);
        expect($response->message)->toBe('Generic API Error'); // Message from generic-error.xml
    });

});
