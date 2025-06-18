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
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with correct status on failure from mock', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/cancel-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->cancelBooking(171838, 'reason');

        expect($response)->toBeInstanceOf(CancelBookingResponse::class);
        expect($response->booking->status)->toBe(BookingStatus::FAILED);
        // Message would be available from the booking details or error response
    });

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with CANCELLED status on successful cancellation', function () {
        // Mock a successful cancellation response
        $successXml = '<?xml version="1.0" encoding="UTF-8"?>
<booking id="12345" identifier="CANCEL-12345" provider_identifier="Provider-1234-54353" arrival="2024-02-08" departure="2024-02-12">
    <name first="John" last="Doe" />
    <email>john@example.com</email>
    <address_1>Test Street 123</address_1>
    <city>Test City</city>
    <country>NL</country>
    <phone>+31123456789</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>0</amount_childs>
    <property id="21663" identifier="1234">Test Property</property>
    <status>cancelled</status>
    <rate>
        <total>100.00</total>
        <final>90.00</final>
        <tax total="10.00">
            <vat>5.00</vat>
            <other>5.00</other>
            <final>100.00</final>
        </tax>
    </rate>
    <created>2024-01-01 10:00:00</created>
    <modified>2024-01-01 11:00:00</modified>
</booking>';

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($successXml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->cancelBooking(12345, 'User request');

        expect($response)->toBeInstanceOf(CancelBookingResponse::class);
        expect($response->booking->status)->toBe(BookingStatus::CANCELLED);
        expect($response->booking->id)->toBe(12345);
    });

    test('BookingManagerAPI::cancelBooking returns CancelBookingResponse with ERROR status on API error response', function () {
        $errorXml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/generic-error.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($errorXml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $this->api->cancelBooking(99999, 'some reason');
    })->throws(\Shelfwood\PhpPms\Exceptions\ApiException::class);

});
