<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Tests\Helpers\TestHelpers; // Corrected import

test('it can fetch calendar for a date range', function () {
    // Use the helper to get the mock file path
    $mockResponsePath = TestHelpers::getMockFilePath('calendar-date-range.xml'); // Corrected filename
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

    $propertyId = 22958; // Corrected expected ID to match mock data
    // Dates should match what the mock CalendarResponse.xml is expected to contain for its first two entries
    // For this example, let's assume the mock covers 2023-10-01 and 2023-10-02
    $startDate = Carbon::parse('2023-10-01');
    $endDate = Carbon::parse('2023-10-02');

    $response = $api->calendar($propertyId, $startDate, $endDate);

    expect($response)->toBeInstanceOf(CalendarResponse::class);
    expect($response->propertyId)->toBe($propertyId);
    expect($response->days)->toBeArray();
    // Assuming CalendarResponse.xml mock for these dates returns 7 days
    expect(count($response->days))->toBe(7); // Corrected count

    // Detailed assertions for the first day
    $firstDay = $response->days[0];
    expect($firstDay)->toBeInstanceOf(CalendarDayInfo::class); // Corrected class
    expect($firstDay->day)->toBeInstanceOf(Carbon::class); // Corrected property: day
    expect($firstDay->day->toDateString())->toBe('2023-11-01'); // Corrected property: day
    expect($firstDay->available)->toBe(0); // Property: available (now ?int) - corrected to 0 based on mock
    expect($firstDay->rate->total)->toBe(173.00); // Corrected property: rate->total for price - corrected to 173.00 based on mock
    expect($firstDay->stayMinimum)->toBe(3); // Corrected property: stayMinimum - corrected to 3 based on mock
    expect($firstDay->maxStay)->toBeNull(); // Property: maxStay (added to CalendarDayInfo) - Corrected to assert null as per mock
    expect($firstDay->closedOnArrival)->toBeNull(); // Property: closedOnArrival (added) - Corrected to assert null
    expect($firstDay->closedOnDeparture)->toBeNull(); // Property: closedOnDeparture (added) - Corrected to assert null
    expect($firstDay->stopSell)->toBeNull(); // Property: stopSell (added) - Corrected to assert null

    // Detailed assertions for the second day
    $secondDay = $response->days[1];
    expect($secondDay)->toBeInstanceOf(CalendarDayInfo::class); // Corrected class
    expect($secondDay->day)->toBeInstanceOf(Carbon::class); // Corrected property: day
    expect($secondDay->day->toDateString())->toBe('2023-11-02'); // Corrected property: day, corrected date
    expect($secondDay->available)->toBe(0); // Corrected availability
    expect($secondDay->rate->total)->toBe(173.00); // Corrected property: rate->total for price, corrected rate
    expect($secondDay->stayMinimum)->toBe(3); // Corrected property: stayMinimum, corrected minimum stay
    expect($secondDay->maxStay)->toBeNull();
    expect($secondDay->closedOnArrival)->toBeNull();
    expect($secondDay->closedOnDeparture)->toBeNull();
    expect($secondDay->stopSell)->toBeNull(); // Corrected to assert null
});
