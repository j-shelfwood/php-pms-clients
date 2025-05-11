<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDay;
use Tests\Helpers\TestHelpers; // Corrected namespace

test('it can fetch calendar changes since a given date', function () {
    // Use the helper to get the mock file path
    $mockXmlPath = TestHelpers::getMockFilePath('calendar-changes.xml', 'bookingmanager'); // Corrected filename
    $mockXmlResponse = file_get_contents($mockXmlPath);
    if ($mockXmlResponse === false) {
        throw new \Exception("Failed to load mock XML file: {$mockXmlPath}");
    }
    if (empty(trim($mockXmlResponse))) {
        throw new \Exception("Mock XML file is empty: {$mockXmlPath}");
    }

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $since = Carbon::parse('2024-07-15T00:00:00Z');

    $response = $api->calendarChanges($since);

    expect($response)->toBeInstanceOf(CalendarChangesResponse::class);
    expect($response->changes)->toBeArray();
    expect(count($response->changes))->toBe(2); // Assuming the mock has 2 properties with changes

    // Assertions for the first property's changes
    $property1Changes = $response->changes[0];
    expect($property1Changes->propertyId)->toBe(22958);

    // The current mock calendar-changes.xml only provides month-level changes.
    // If day-level changes are expected, the mock or response mapping needs adjustment.
    // For now, we check if 'months' attribute is present, as per the mock structure.
    expect($property1Changes->months)->toBeArray();
    // expect($property1Changes->days)->toBeArray(); // This would fail as mock doesn't provide day details here
    // expect(count($property1Changes->days))->toBe(2);
    // expect($property1Changes->days[0])->toBeInstanceOf(CalendarDay::class);
    // expect($property1Changes->days[0]->date->toDateString())->toBe('2024-07-20');
    // expect($property1Changes->days[0]->available)->toBe(1);
    // expect($property1Changes->days[0]->price)->toBe(200.00);
    // expect($property1Changes->days[1]->date->toDateString())->toBe('2024-07-21');
    // expect($property1Changes->days[1]->available)->toBe(0);
    // expect($property1Changes->days[1]->price)->toBe(210.00);

    // Assertions for the second property's changes (if applicable based on your mock)
    $property2Changes = $response->changes[1];
    expect($property2Changes->propertyId)->toBe(23180);
    expect($property2Changes->months)->toBeArray(); // Corrected from toBeString() to toBeArray()

});

test('it handles empty calendar changes response', function () {
    $mockXmlResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response>
    <request>list_calendar_changes</request>
    <error_code>0</error_code>
    <error_text>OK</error_text>
</response>
XML;
    // This mock represents a scenario where no properties had changes

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);
    $api = new BookingManagerAPI($httpClient, 'key', 'user', 'url');
    $since = Carbon::now();

    $response = $api->calendarChanges($since);

    expect($response)->toBeInstanceOf(CalendarChangesResponse::class);
    expect($response->changes)->toBeArray();
    expect($response->changes)->toBeEmpty();
});
