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
use Tests\Helpers\TestHelpers;


describe('CalendarEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('BookingManagerAPI::calendar returns CalendarResponse with empty days due to deprecated endpoint', function () {
        // No need to mock HTTP response since calendar() now returns empty data directly
        // due to deprecated calendar.xml endpoint (as of API version 1.0.3)

        $start = Carbon::parse('2023-11-01');
        $end = Carbon::parse('2023-11-07');
        $response = $this->api->calendar(22958, $start, $end);

        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->propertyId)->toBe(22958);
        expect($response->days)->toBeArray();
        expect($response->days)->toBeEmpty(); // Changed expectation: empty due to deprecated endpoint

        // Note: The calendar.xml endpoint was deprecated in API version 1.0.3
        // Future implementation should use info.xml + availability.xml combination
    });
    // Removed test for HttpClientException (no longer used in new exception hierarchy)

});
