<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\SeasonType; // Added import
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;


describe('CalendarEndpointTest', function () {
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

    test('BookingManagerAPI::calendar returns CalendarResponse with days', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/calendar-date-range.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $start = Carbon::parse('2023-11-01');
        $end = Carbon::parse('2023-11-07');
        $response = $this->api->calendar(22958, $start, $end);

        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->propertyId)->toBe(22958);
        expect($response->days)->toBeArray();
        expect($response->days)->not->toBeEmpty();
        $first = $response->days[0];
        expect($first)->toBeInstanceOf(CalendarDayInfo::class);
        expect($first->day->format('Y-m-d'))->toBe('2023-11-01');
        expect($first->season)->toBeInstanceOf(SeasonType::class); // Added assertion
        expect($first->season)->toBe(SeasonType::HIGH); // Added assertion
    });
    test('BookingManagerAPI::calendar throws HttpClientException on generic API error', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/generic-error.xml');
        $mockResp = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($xml);
        $mockResp->method('getBody')->willReturn($stream);
        $this->mockHttpClient->method('request')->willReturn($mockResp);
        $this->api->calendar(1, Carbon::now(), Carbon::now());
    })->throws(\Shelfwood\PhpPms\Exceptions\HttpClientException::class);

    });
