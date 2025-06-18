<?php

require 'vendor/autoload.php';

use Shelfwood\PhpPms\Http\XMLParser;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Carbon\Carbon;
use ReflectionClass;
use ReflectionMethod;

// Mock XML response from test
$mockXml = '<?xml version="1.0" encoding="UTF-8"?>
<response>
    <unavailable property_id="21663">
        <start>2024-02-20</start>
        <end>2024-02-20</end>
        <modified>2024-02-19 10:00:00</modified>
    </unavailable>
</response>';

echo "=== Debug Availability XML Parsing ===" . PHP_EOL;

// Parse the XML
$parsedData = XMLParser::parse($mockXml);
echo "Parsed Data:" . PHP_EOL;
print_r($parsedData);

$startDate = Carbon::parse('2024-02-19');
$endDate = Carbon::parse('2024-02-20');

echo PHP_EOL . "Date Range: {$startDate->toDateString()} to {$endDate->toDateString()}" . PHP_EOL;

// Use reflection to call the private mapFromAvailabilityResponse method directly
$reflection = new ReflectionClass(CalendarResponse::class);
$method = $reflection->getMethod('mapFromAvailabilityResponse');
$method->setAccessible(true);

echo PHP_EOL . "Calling mapFromAvailabilityResponse directly..." . PHP_EOL;
$response = $method->invokeArgs(null, [$parsedData, $startDate, $endDate]);

echo "Property ID: {$response->propertyId}" . PHP_EOL;
echo "Number of days: " . count($response->days) . PHP_EOL;

foreach ($response->days as $i => $day) {
    echo "Day {$i}: {$day->day->toDateString()} - Available: {$day->available}" . PHP_EOL;
}