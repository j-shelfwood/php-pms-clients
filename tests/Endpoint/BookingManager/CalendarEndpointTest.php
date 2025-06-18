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

// Import the Golden Master assertion function
use function Tests\Helpers\assertCalendarResponseMatchesExpected;


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

    test('Golden Master: calendar correctly maps all fields from rich response', function () {
        // Use the calendar-date-range.xml mock which contains rate information
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/calendar-date-range.xml');

        $mockHttpResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockHttpResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockHttpResponse);

        $startDate = Carbon::parse('2023-11-01');
        $endDate = Carbon::parse('2023-11-07');

        // Call the calendar method
        $response = $this->api->calendar(22958, $startDate, $endDate);

        expect($response)->toBeInstanceOf(CalendarResponse::class);

        // Golden Master validation - validates ALL fields
        assertCalendarResponseMatchesExpected($response);
    });

    test('BookingManagerAPI::availability uses availability.xml and correctly maps availability data', function () {
        // Create a mock XML response for availability.xml with unavailable period
        $mockXml = '<?xml version="1.0" encoding="UTF-8"?>
<response>
    <unavailable property_id="21663">
        <start>2024-02-20</start>
        <end>2024-02-20</end>
        <modified>2024-02-19 10:00:00</modified>
    </unavailable>
</response>';

        $mockHttpResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($mockXml);
        $mockHttpResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockHttpResponse);

        $startDate = Carbon::parse('2024-02-19');
        $endDate = Carbon::parse('2024-02-20');

        // Call the availability method (not calendar)
        $response = $this->api->availability(21663, $startDate, $endDate);

        // Assertions
        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->propertyId)->toBe(21663);
        expect($response->days)->toBeArray();
        expect($response->days)->not()->toBeEmpty();

        // Check that we have availability data for the requested dates (2 days)
        expect($response->days)->toHaveCount(2);

        $firstDay = $response->days[0];
        expect($firstDay)->toBeInstanceOf(CalendarDayInfo::class);
        expect($firstDay->available)->toBe(1); // Available (not in unavailable range)
        expect($firstDay->day->toDateString())->toBe('2024-02-19');
        expect($firstDay->rate->final)->toBe(0.0); // No rate data from availability.xml

        $secondDay = $response->days[1];
        expect($secondDay)->toBeInstanceOf(CalendarDayInfo::class);
        expect($secondDay->available)->toBe(0); // Unavailable (in unavailable range)
        expect($secondDay->day->toDateString())->toBe('2024-02-20');
        expect($secondDay->rate->final)->toBe(0.0); // No rate data from availability.xml
    });

});
