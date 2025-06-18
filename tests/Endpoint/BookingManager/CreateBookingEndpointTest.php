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

    });
