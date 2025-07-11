<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;
use Tests\Helpers\TestHelpers;

// Import the Golden Master assertion function
use function Tests\Helpers\assertCalendarChangesResponseMatchesExpected;


describe('CalendarChangesEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: calendarChanges correctly maps all fields from rich response', function () {
        $mockResponsePath = TestHelpers::getMockFilePath('calendar-changes.xml');
        $xml = file_get_contents($mockResponsePath);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $since = Carbon::parse('2023-11-12 00:00:00');
        $response = $this->api->calendarChanges($since);

        expect($response)->toBeInstanceOf(CalendarChangesResponse::class);

        // Golden Master validation - validates ALL fields
        assertCalendarChangesResponseMatchesExpected($response);
    });
});
