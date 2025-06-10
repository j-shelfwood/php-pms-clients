<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
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
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('finalizeBooking returns FinalizeBookingResponse with OPEN status for open booking mock', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/create-booking.xml'); // This mock has <status>open</status>
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->finalizeBooking(171830);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->bookingId)->toBe(171830);
        expect($response->identifier)->toBe('BILL-171830-148-AMS-21663-2024-02-08');
        expect($response->status)->toBe(BookingStatus::OPEN);
        expect($response->message)->toBe(''); // No message tag in create-booking.xml at booking root
    });

    test('finalizeBooking returns FinalizeBookingResponse with SUCCESS status for implicit success mock with message', function () {
        $xml = '<response><booking id="12345" identifier="FINAL-12345"><message>Custom success message.</message></booking></response>';
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->finalizeBooking(12345);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->bookingId)->toBe(12345);
        expect($response->identifier)->toBe('FINAL-12345');
        expect($response->status)->toBe(BookingStatus::SUCCESS); // Defaulted to SUCCESS due to id/identifier and no status tag
        expect($response->message)->toBe('Custom success message.');
    });

    test('finalizeBooking returns FinalizeBookingResponse with SUCCESS status and default message for implicit success with no message tag', function () {
        $xml = '<response><booking id="54321" identifier="IMPLICIT-SUCCESS"></booking></response>'; // No status, no message
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->finalizeBooking(54321);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->bookingId)->toBe(54321);
        expect($response->identifier)->toBe('IMPLICIT-SUCCESS');
        expect($response->status)->toBe(BookingStatus::SUCCESS);
        expect($response->message)->toBe('Booking finalized successfully.'); // Default message from map logic
    });

    test('finalizeBooking throws ApiException on generic API error', function () {
        $mockResponsePath = Tests\Helpers\TestHelpers::getMockFilePath('generic-error.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $this->api->finalizeBooking(12345);
    })->throws(\Shelfwood\PhpPms\Exceptions\ApiException::class);
});
