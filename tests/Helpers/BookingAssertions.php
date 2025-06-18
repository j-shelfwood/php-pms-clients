<?php

namespace Tests\Helpers;

use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingDetails;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingRate;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingTax;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

function assertBookingDetailsMatchesExpected(BookingDetails $actualBooking): void
{
    $expected = TestData::getExpectedBookingData();

    // Basic booking information
    expect($actualBooking->id)->toBe($expected['id']);
    expect($actualBooking->identifier)->toBe($expected['identifier']);
    expect($actualBooking->provider_identifier)->toBe($expected['provider_identifier']);
    expect($actualBooking->channel_identifier)->toBe($expected['channel_identifier']);

    // Dates
    expect($actualBooking->arrival)->toBeInstanceOf(Carbon::class);
    expect($actualBooking->arrival->format('Y-m-d'))->toBe($expected['arrival']);
    expect($actualBooking->departure)->toBeInstanceOf(Carbon::class);
    expect($actualBooking->departure->format('Y-m-d'))->toBe($expected['departure']);

    // Guest information
    expect($actualBooking->first_name)->toBe($expected['first_name']);
    expect($actualBooking->last_name)->toBe($expected['last_name']);
    expect($actualBooking->email)->toBe($expected['email']);
    expect($actualBooking->address_1)->toBe($expected['address_1']);
    expect($actualBooking->address_2)->toBe($expected['address_2']);
    expect($actualBooking->city)->toBe($expected['city']);
    expect($actualBooking->country)->toBe($expected['country']);
    expect($actualBooking->phone)->toBe($expected['phone']);

    // Capacity
    expect($actualBooking->amount_adults)->toBe($expected['amount_adults']);
    expect($actualBooking->amount_children)->toBe($expected['amount_children']);

    // Optional fields
    expect($actualBooking->time_arrival)->toBe($expected['time_arrival']);
    expect($actualBooking->flight)->toBe($expected['flight']);
    expect($actualBooking->notes)->toBe($expected['notes']);

    // Property information
    expect($actualBooking->property_id)->toBe($expected['property_id']);
    expect($actualBooking->property_identifier)->toBe($expected['property_identifier']);
    expect($actualBooking->property_name)->toBe($expected['property_name']);

    // Status
    expect($actualBooking->status)->toBeInstanceOf(BookingStatus::class);
    expect($actualBooking->status->value)->toBe($expected['status']);

    // Timestamps
    expect($actualBooking->created)->toBeInstanceOf(Carbon::class);
    expect($actualBooking->created->toISOString())->toBe($expected['created']);
    expect($actualBooking->modified)->toBeInstanceOf(Carbon::class);
    expect($actualBooking->modified->toISOString())->toBe($expected['modified']);

    // Rate information
    assertBookingRateMatchesExpected($actualBooking->rate, $expected['rate']);
}

function assertBookingRateMatchesExpected(BookingRate $actualRate, array $expected): void
{
    expect($actualRate->total)->toBe($expected['total']);
    expect($actualRate->final)->toBe($expected['final']);
    expect($actualRate->prepayment)->toBe($expected['prepayment']);
    expect($actualRate->balance_due)->toBe($expected['balance_due']);
    expect($actualRate->fee)->toBe($expected['fee']);

    // Tax information
    assertBookingTaxMatchesExpected($actualRate->tax, $expected['tax']);
}

function assertBookingTaxMatchesExpected(BookingTax $actualTax, array $expected): void
{
    expect($actualTax->total)->toBe($expected['total']);
    expect($actualTax->vat)->toBe($expected['vat']);
    expect($actualTax->other)->toBe($expected['other']);
    expect($actualTax->final)->toBe($expected['final']);
}