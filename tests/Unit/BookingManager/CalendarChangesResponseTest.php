<?php

declare(strict_types=1);

namespace Tests\Unit\BookingManager;

use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Shelfwood\PhpPms\Http\XMLParser;

describe('CalendarChangesResponse::map', function () {
    test('parses multiple change types with multiple properties', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="details" amount="3" ids="23638,23639,23640" time="2026-01-25 10:53:50"/>
  <change type="availability" amount="3" ids="23638,23639,23640" time="2026-01-25 16:20:10"/>
  <change type="rate" amount="3" ids="23638,23639,23640" time="2026-01-25 10:53:50"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(3);

        // Check first property
        expect($response->changes[0])->toBeInstanceOf(CalendarChange::class);
        expect($response->changes[0]->propertyId)->toBe(23638);

        // Check second property
        expect($response->changes[1]->propertyId)->toBe(23639);

        // Check third property
        expect($response->changes[2]->propertyId)->toBe(23640);

        // Amount should match number of unique properties
        expect($response->amount)->toBe(3);

        // Time should be the latest change time
        expect($response->time)->not->toBeNull();
        expect($response->time->format('Y-m-d H:i:s'))->toBe('2026-01-25 16:20:10');
    });

    test('parses single change type with single property', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" amount="1" ids="23638" time="2026-01-25 16:20:10"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(1);
        expect($response->changes[0]->propertyId)->toBe(23638);
        expect($response->amount)->toBe(1);
        expect($response->time->format('Y-m-d H:i:s'))->toBe('2026-01-25 16:20:10');
    });

    test('parses response with no changes', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(0);
        expect($response->amount)->toBe(0);
        expect($response->time)->toBeNull();
    });

    test('handles duplicate property IDs across change types', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="details" amount="2" ids="23638,23639" time="2026-01-25 10:00:00"/>
  <change type="availability" amount="3" ids="23638,23639,23640" time="2026-01-25 11:00:00"/>
  <change type="rate" amount="2" ids="23638,23640" time="2026-01-25 12:00:00"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        // Should have 3 unique properties (23638, 23639, 23640)
        expect($response->changes)->toHaveCount(3);

        $propertyIds = $response->changes->map(fn($c) => $c->propertyId)->sort()->values();
        expect($propertyIds->toArray())->toBe([23638, 23639, 23640]);

        // Time should be the latest
        expect($response->time->format('Y-m-d H:i:s'))->toBe('2026-01-25 12:00:00');
    });

    test('handles single change element without array wrapper', function () {
        // When XML has only one <change>, parser may not wrap it in array
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" amount="1" ids="23638" time="2026-01-25 16:20:10"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(1);
        expect($response->changes[0]->propertyId)->toBe(23638);
    });

    test('handles whitespace in ids attribute', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" amount="3" ids=" 23638 , 23639 , 23640 " time="2026-01-25 16:20:10"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(3);
        expect($response->changes[0]->propertyId)->toBe(23638);
        expect($response->changes[1]->propertyId)->toBe(23639);
        expect($response->changes[2]->propertyId)->toBe(23640);
    });

    test('handles missing optional attributes gracefully', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" ids="23638"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(1);
        expect($response->changes[0]->propertyId)->toBe(23638);
        expect($response->time)->toBeNull();
    });

    test('handles invalid structure gracefully', function () {
        $parsed = ['invalid' => 'structure'];

        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toBeEmpty()
            ->and($response->amount)->toBe(0)
            ->and($response->time)->toBeNull();
    });

    test('handles empty ids attribute', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" amount="0" ids="" time="2026-01-25 16:20:10"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(0);
        expect($response->amount)->toBe(0);
    });

    test('handles numeric property IDs correctly', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="availability" ids="1,999,12345" time="2026-01-25 16:20:10"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        expect($response->changes)->toHaveCount(3);
        expect($response->changes[0]->propertyId)->toBe(1);
        expect($response->changes[1]->propertyId)->toBe(999);
        expect($response->changes[2]->propertyId)->toBe(12345);
    });

    test('time attribute uses latest timestamp across all changes', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<changes>
  <change type="details" ids="23638" time="2026-01-25 08:00:00"/>
  <change type="availability" ids="23639" time="2026-01-25 12:00:00"/>
  <change type="rate" ids="23640" time="2026-01-25 10:00:00"/>
</changes>
XML;

        $parsed = XMLParser::parse($xml);
        $response = CalendarChangesResponse::map($parsed);

        // Should use the latest time (12:00:00)
        expect($response->time->format('Y-m-d H:i:s'))->toBe('2026-01-25 12:00:00');
    });
});
