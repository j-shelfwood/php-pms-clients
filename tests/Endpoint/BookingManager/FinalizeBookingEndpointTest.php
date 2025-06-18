<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\BookingManager\Responses\FinalizeBookingResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

// Import the Golden Master assertion function
use function Tests\Helpers\assertBookingDetailsMatchesExpected;


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

    test('Golden Master: finalizeBooking correctly maps all fields from rich response', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/create-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->finalizeBooking(171830);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);

        // Golden Master validation - validates ALL fields
        assertBookingDetailsMatchesExpected($response->booking);
    });

    test('finalizeBooking returns FinalizeBookingResponse with SUCCESS status for implicit success mock with message', function () {
        // Mock a successful finalization response
        $successXml = '<?xml version="1.0" encoding="UTF-8"?>
<booking id="12345" identifier="FINAL-12345" provider_identifier="Provider-1234-54353" arrival="2024-02-08" departure="2024-02-12">
    <name first="John" last="Doe" />
    <email>john@example.com</email>
    <address_1>Test Street 123</address_1>
    <city>Test City</city>
    <country>NL</country>
    <phone>+31123456789</phone>
    <amount_adults>2</amount_adults>
    <amount_childs>0</amount_childs>
    <property id="21663" identifier="1234">Test Property</property>
    <status>success</status>
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

        $response = $this->api->finalizeBooking(12345);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->booking->id)->toBe(12345);
        expect($response->booking->identifier)->toBe('FINAL-12345');
        expect($response->booking->status)->toBe(BookingStatus::SUCCESS);
    });

    test('finalizeBooking returns FinalizeBookingResponse with SUCCESS status and default message for implicit success with no message tag', function () {
        // Mock an implicit success response (with valid booking data but no explicit status)
        $implicitSuccessXml = '<?xml version="1.0" encoding="UTF-8"?>
<booking id="54321" identifier="IMPLICIT-SUCCESS" provider_identifier="Provider-1234-54353" arrival="2024-02-08" departure="2024-02-12">
    <name first="Jane" last="Smith" />
    <email>jane@example.com</email>
    <address_1>Test Street 456</address_1>
    <city>Test City</city>
    <country>NL</country>
    <phone>+31987654321</phone>
    <amount_adults>1</amount_adults>
    <amount_childs>1</amount_childs>
    <property id="21663" identifier="1234">Test Property</property>
    <status>success</status>
    <rate>
        <total>150.00</total>
        <final>135.00</final>
        <tax total="15.00">
            <vat>7.50</vat>
            <other>7.50</other>
            <final>150.00</final>
        </tax>
    </rate>
    <created>2024-01-01 09:00:00</created>
    <modified>2024-01-01 10:00:00</modified>
</booking>';

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($implicitSuccessXml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->finalizeBooking(54321);

        expect($response)->toBeInstanceOf(FinalizeBookingResponse::class);
        expect($response->booking->id)->toBe(54321);
        expect($response->booking->identifier)->toBe('IMPLICIT-SUCCESS');
        expect($response->booking->status)->toBe(BookingStatus::SUCCESS);
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
