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

        test('BookingManagerAPI::calendar uses availability.xml and correctly maps availability data', function () {
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

        // Call the calendar method
        $response = $this->api->calendar(21663, $startDate, $endDate);

        // Assertions
        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->propertyId)->toBe(21663);
        expect($response->days)->toBeArray();
        expect($response->days)->not()->toBeEmpty();

        // Check that we have calendar data for the requested dates (2 days)
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

    test('BookingManagerAPI::calendar handles empty availability response (all dates available)', function () {
        // Create a mock XML response for availability.xml with no unavailable periods
        $mockXml = '<?xml version="1.0" encoding="UTF-8"?>
<response>
</response>';

        $mockHttpResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($mockXml);
        $mockHttpResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockHttpResponse);

        $startDate = Carbon::parse('2024-02-19');
        $endDate = Carbon::parse('2024-02-20');

        // Call the calendar method
        $response = $this->api->calendar(21663, $startDate, $endDate);

        // Assertions
        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->days)->toBeArray();
        expect($response->days)->not()->toBeEmpty();

        // Check that we have calendar data for the requested dates (2 days)
        expect($response->days)->toHaveCount(2);

        // All days should be available since no unavailable periods are specified
        $firstDay = $response->days[0];
        expect($firstDay)->toBeInstanceOf(CalendarDayInfo::class);
        expect($firstDay->available)->toBe(1); // Available
        expect($firstDay->day->toDateString())->toBe('2024-02-19');

        $secondDay = $response->days[1];
        expect($secondDay)->toBeInstanceOf(CalendarDayInfo::class);
        expect($secondDay->available)->toBe(1); // Available
        expect($secondDay->day->toDateString())->toBe('2024-02-20');
    });

});
