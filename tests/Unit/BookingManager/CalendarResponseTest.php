<?php

declare(strict_types=1);

namespace Tests\Unit\BookingManager;

use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Shelfwood\PhpPms\Http\XMLParser;
use Shelfwood\PhpPms\Exceptions\MappingException;
use Carbon\Carbon;
use Tests\Helpers\TestHelpers;

describe('CalendarResponse::map', function () {
    test('parses calendar.xml format with multiple days', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('calendar-date-range.xml'));
        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBeInt()->toBeGreaterThan(0)
            ->and($response->days)->toBeArray()->not->toBeEmpty();

        // Verify all days are CalendarDayInfo instances
        foreach ($response->days as $day) {
            expect($day)->toBeInstanceOf(CalendarDayInfo::class)
                ->and($day->day)->toBeInstanceOf(Carbon::class)
                ->and($day->available)->toBeInt();
        }
    });

    test('extracts correct property ID from calendar', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('calendar-date-range.xml'));
        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response->propertyId)->toBe(22958);
    });

    test('parses availability.xml format with date range', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unavailable>
    <unavailable property_id="12345">
        <start>2024-01-10</start>
        <end>2024-01-15</end>
        <modified>2024-01-01 10:00:00</modified>
    </unavailable>
</unavailable>
XML;

        $parsed = XMLParser::parse($xml);
        $startDate = Carbon::parse('2024-01-08');
        $endDate = Carbon::parse('2024-01-18');

        $response = CalendarResponse::map($parsed, $startDate, $endDate);

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe(12345)
            ->and($response->days)->toHaveCount(11); // 8th to 18th inclusive

        // Check availability pattern
        $availableDays = array_filter($response->days, fn($d) => $d->available === 1);
        $unavailableDays = array_filter($response->days, fn($d) => $d->available === 0);

        // Jan 8-9 and Jan 16-18 should be available (2 + 3 = 5 days)
        expect($availableDays)->toHaveCount(5);
        // Jan 10-15 should be unavailable (6 days)
        expect($unavailableDays)->toHaveCount(6);
    });

    test('handles single day calendar', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<calendars>
    <calendar property_id="99">
        <info day="2024-01-01" season="low" modified="2024-01-01 00:00:00">
            <available>1</available>
            <stay_minimum>1</stay_minimum>
            <rate percentage="100" currency="EUR">
                <total>100.00</total>
                <final>100.00</final>
            </rate>
        </info>
    </calendar>
</calendars>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response->days)->toHaveCount(1)
            ->and($response->propertyId)->toBe(99)
            ->and($response->days[0]->day->format('Y-m-d'))->toBe('2024-01-01')
            ->and($response->days[0]->available)->toBe(1);
    });

    test('handles empty calendar gracefully', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<calendars>
    <calendar property_id="123"/>
</calendars>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response->propertyId)->toBe(123)
            ->and($response->days)->toBeArray()->toBeEmpty();
    });

    test('handles multiple unavailable periods in availability.xml', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unavailable>
    <unavailable property_id="456">
        <start>2024-01-05</start>
        <end>2024-01-07</end>
        <modified>2024-01-01 10:00:00</modified>
    </unavailable>
    <unavailable property_id="456">
        <start>2024-01-15</start>
        <end>2024-01-17</end>
        <modified>2024-01-01 11:00:00</modified>
    </unavailable>
</unavailable>
XML;

        $parsed = XMLParser::parse($xml);
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-20');

        $response = CalendarResponse::map($parsed, $startDate, $endDate);

        expect($response->propertyId)->toBe(456)
            ->and($response->days)->toHaveCount(20);

        // Count unavailable days (should be 6: Jan 5-7 and Jan 15-17)
        $unavailableDays = array_filter($response->days, fn($d) => $d->available === 0);
        expect(count($unavailableDays))->toBe(6);
    });

    test('handles missing calendar data throws exception', function () {
        $parsed = ['empty' => 'response'];

        expect(fn() => CalendarResponse::map($parsed))
            ->toThrow(MappingException::class);
    });

    test('parses multiple calendar days in sequence', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<calendars>
    <calendar property_id="789">
        <info day="2024-01-01" season="low" modified="2024-01-01 00:00:00">
            <available>1</available>
            <stay_minimum>1</stay_minimum>
        </info>
        <info day="2024-01-02" season="low" modified="2024-01-01 00:00:00">
            <available>1</available>
            <stay_minimum>1</stay_minimum>
        </info>
        <info day="2024-01-03" season="low" modified="2024-01-01 00:00:00">
            <available>0</available>
            <stay_minimum>2</stay_minimum>
        </info>
    </calendar>
</calendars>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response->days)->toHaveCount(3);
        expect($response->days[0]->day->format('Y-m-d'))->toBe('2024-01-01');
        expect($response->days[1]->day->format('Y-m-d'))->toBe('2024-01-02');
        expect($response->days[2]->day->format('Y-m-d'))->toBe('2024-01-03');
    });

    test('days are ordered chronologically', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('calendar-date-range.xml'));
        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        $previousDate = null;
        foreach ($response->days as $day) {
            if ($previousDate !== null) {
                expect($day->day->greaterThanOrEqualTo($previousDate))->toBeTrue();
            }
            $previousDate = $day->day;
        }
    });

    test('availability.xml without unavailable periods returns all available', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unavailable/>
XML;

        $parsed = XMLParser::parse($xml);
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-05');

        $response = CalendarResponse::map($parsed, $startDate, $endDate);

        expect($response->days)->toHaveCount(5);

        // All days should be available
        foreach ($response->days as $day) {
            expect($day->available)->toBe(1)
                ->and($day->closedOnArrival)->toBeFalse()
                ->and($day->closedOnDeparture)->toBeFalse()
                ->and($day->stopSell)->toBeFalse();
        }
    });

    test('handles nested calendar structure variations', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<calendars>
    <calendar>
        <info day="2024-01-01" season="low" modified="2024-01-01 00:00:00" id="111">
            <available>1</available>
            <stay_minimum>2</stay_minimum>
        </info>
    </calendar>
</calendars>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarResponse::map($parsed);

        expect($response->days)->toHaveCount(1);
    });

    test('throws exception on malformed availability date', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<unavailable>
    <unavailable property_id="999">
        <start>invalid-date</start>
        <end>2024-01-10</end>
    </unavailable>
</unavailable>
XML;

        $parsed = XMLParser::parse($xml);
        $startDate = Carbon::parse('2024-01-01');
        $endDate = Carbon::parse('2024-01-10');

        expect(fn() => CalendarResponse::map($parsed, $startDate, $endDate))
            ->toThrow(MappingException::class);
    });
});
