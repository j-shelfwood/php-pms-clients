<?php

namespace Tests\Helpers;

use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarRate;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarTax;
use Shelfwood\PhpPms\BookingManager\Enums\SeasonType;

function assertCalendarResponseMatchesExpected(CalendarResponse $actualCalendar): void
{
    $expected = TestData::getExpectedCalendarData();

    expect($actualCalendar->propertyId)->toBe($expected['propertyId']);
    expect($actualCalendar->days)->toBeArray();
    expect($actualCalendar->days)->not()->toBeEmpty();

    // Validate first day structure
    if (!empty($expected['days'])) {
        assertCalendarDayInfoMatchesExpected($actualCalendar->days[0], $expected['days'][0]);
    }
}

function assertCalendarDayInfoMatchesExpected(CalendarDayInfo $actualDay, array $expectedDay): void
{
    expect($actualDay->day)->toBeInstanceOf(Carbon::class);
    expect($actualDay->day->format('Y-m-d'))->toBe($expectedDay['day']);

    if ($expectedDay['season']) {
        expect($actualDay->season)->toBeInstanceOf(SeasonType::class);
        expect($actualDay->season->value)->toBe($expectedDay['season']);
    } else {
        expect($actualDay->season)->toBeNull();
    }

    expect($actualDay->modified)->toBeInstanceOf(Carbon::class);
    expect($actualDay->modified->toISOString())->toBe($expectedDay['modified']);
    expect($actualDay->available)->toBe($expectedDay['available']);
    expect($actualDay->stayMinimum)->toBe($expectedDay['stayMinimum']);
    expect($actualDay->maxStay)->toBe($expectedDay['maxStay']);
    expect($actualDay->closedOnArrival)->toBe($expectedDay['closedOnArrival']);
    expect($actualDay->closedOnDeparture)->toBe($expectedDay['closedOnDeparture']);
    expect($actualDay->stopSell)->toBe($expectedDay['stopSell']);

    // Rate information
    assertCalendarRateMatchesExpected($actualDay->rate, $expectedDay['rate']);
}

function assertCalendarRateMatchesExpected(CalendarRate $actualRate, array $expectedRate): void
{
    expect($actualRate->percentage)->toBe($expectedRate['percentage']);
    expect($actualRate->currency)->toBe($expectedRate['currency']);
    expect($actualRate->total)->toBe($expectedRate['total']);
    expect($actualRate->final)->toBe($expectedRate['final']);
    expect($actualRate->fee)->toBe($expectedRate['fee']);
    expect($actualRate->prepayment)->toBe($expectedRate['prepayment']);
    expect($actualRate->balanceDue)->toBe($expectedRate['balanceDue']);

    // Tax information
    assertCalendarTaxMatchesExpected($actualRate->tax, $expectedRate['tax']);
}

function assertCalendarTaxMatchesExpected(CalendarTax $actualTax, array $expectedTax): void
{
    expect($actualTax->total)->toBe($expectedTax['total']);
    expect($actualTax->other)->toBe($expectedTax['other']);
    expect($actualTax->otherType)->toBe($expectedTax['otherType']);
    expect($actualTax->otherValue)->toBe($expectedTax['otherValue']);
    expect($actualTax->vat)->toBe($expectedTax['vat']);
    expect($actualTax->vatValue)->toBe($expectedTax['vatValue']);
    expect($actualTax->final)->toBe($expectedTax['final']);
}