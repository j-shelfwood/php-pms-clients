<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\SeasonType;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Tests\Helpers\TestHelpers;

test('it can fetch calendar for a date range', function () {
    $mockResponsePath = TestHelpers::getMockFilePath('calendar-date-range.xml');
    $mockXmlResponse = file_get_contents($mockResponsePath);

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded.');

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 22958;
    $startDate = Carbon::parse('2023-10-01');
    $endDate = Carbon::parse('2023-10-02');

    $response = $api->calendar($propertyId, $startDate, $endDate);

    expect($response)->toBeInstanceOf(CalendarResponse::class);
    expect($response->propertyId)->toBe($propertyId);
    expect($response->days)->toBeArray();
    expect(count($response->days))->toBe(7);

    $firstDay = $response->days[0];
    expect($firstDay)->toBeInstanceOf(CalendarDayInfo::class);
    expect($firstDay->day)->toBeInstanceOf(Carbon::class);
    expect($firstDay->day->toDateString())->toBe('2023-11-01');
    expect($firstDay->season)->toBeInstanceOf(SeasonType::class);
    expect($firstDay->season)->toBe(SeasonType::HIGH);
    expect($firstDay->available)->toBe(0);
    expect($firstDay->rate->total)->toBe(173.00);
    expect($firstDay->stayMinimum)->toBe(3);
    expect($firstDay->maxStay)->toBeNull();
    expect($firstDay->closedOnArrival)->toBeNull();
    expect($firstDay->closedOnDeparture)->toBeNull();
    expect($firstDay->stopSell)->toBeNull();

    $secondDay = $response->days[1];
    expect($secondDay)->toBeInstanceOf(CalendarDayInfo::class);
    expect($secondDay->day)->toBeInstanceOf(Carbon::class);
    expect($secondDay->day->toDateString())->toBe('2023-11-02');
    expect($secondDay->season)->toBeInstanceOf(SeasonType::class);
    expect($secondDay->season)->toBe(SeasonType::HIGH);
    expect($secondDay->available)->toBe(0);
    expect($secondDay->rate->total)->toBe(173.00);
    expect($secondDay->stayMinimum)->toBe(3);
    expect($secondDay->maxStay)->toBeNull();
    expect($secondDay->closedOnArrival)->toBeNull();
    expect($secondDay->closedOnDeparture)->toBeNull();
    expect($secondDay->stopSell)->toBeNull();
});
