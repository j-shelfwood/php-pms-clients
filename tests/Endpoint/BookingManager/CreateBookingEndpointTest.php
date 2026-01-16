<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Payloads\CreateBookingPayload;
use Shelfwood\PhpPms\BookingManager\Responses\CreateBookingResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

// Import the Golden Master assertion function
use function Tests\Helpers\assertBookingDetailsMatchesExpected;


describe('CreateBookingEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: createBooking correctly maps all fields from rich response', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/create-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $payload = new CreateBookingPayload(
            21663,
            '2024-02-08',
            '2024-02-12',
            'Joris',
            'Schelfhout',
            'joris@shelfwood.co',
            'Fagelstraat 83H',
            'Amsterdam',
            'NL',
            '+31648353484',
            1
        );
        $response = $this->api->createBooking($payload);

        expect($response)->toBeInstanceOf(CreateBookingResponse::class);

        // Golden Master validation - validates ALL fields
        assertBookingDetailsMatchesExpected($response->booking);
    });

    test('createBooking with custom rate fields passes them to API', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/create-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);

        // Capture the request parameters to verify rate fields are included
        $this->mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://dummy-url/booking_create.xml',
                $this->callback(function ($options) {
                    $params = $options['form_params'] ?? [];
                    return isset($params['rate_final'])
                        && $params['rate_final'] === 2891.31
                        && isset($params['rate_incl'])
                        && $params['rate_incl'] === 0
                        && isset($params['rate_prepayment'])
                        && $params['rate_prepayment'] === 1157.97
                        && isset($params['balance_due'])
                        && $params['balance_due'] === 2701.93;
                })
            )
            ->willReturn($mockResponse);

        $payload = new CreateBookingPayload(
            property_id: 21663,
            start: '2024-02-08',
            end: '2024-02-12',
            name_first: 'Hendrik',
            name_last: 'van Werven',
            email: 'erik@rvst.com.au',
            address_1: 'Unit 705/81 Macleay Street',
            city: 'Potts Point',
            country: 'AU',
            phone: '0405053799',
            amount_adults: 2,
            rate_final: 2891.31,
            rate_incl: 0,
            rate_prepayment: 1157.97,
            balance_due: 2701.93
        );

        $response = $this->api->createBooking($payload);
        expect($response)->toBeInstanceOf(CreateBookingResponse::class);
    });

    });
