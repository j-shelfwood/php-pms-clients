<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;

// Import the Golden Master assertion function
use function Tests\Helpers\assertRateResponseMatchesExpected;


describe('RateForStayEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: rateForStay correctly maps all fields from rich response', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/get-rate-for-stay.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $arrival = Carbon::parse('2024-02-19');
        $departure = Carbon::parse('2024-02-20');
        $response = $this->api->rateForStay(21663, $arrival, $departure, 1);

        expect($response)->toBeInstanceOf(RateResponse::class);

        // Golden Master validation - validates ALL fields
        assertRateResponseMatchesExpected($response);
    });

    });
