<?php

namespace Tests\Helpers;

use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;

function assertRateResponseMatchesExpected(RateResponse $actualRate): void
{
    $expected = TestData::getExpectedRateData();

    expect($actualRate->final_before_taxes)->toBe($expected['final_before_taxes']);
    expect($actualRate->final_after_taxes)->toBe($expected['final_after_taxes']);
    expect($actualRate->tax_vat)->toBe($expected['tax_vat']);
    expect($actualRate->tax_other)->toBe($expected['tax_other']);
    expect($actualRate->tax_total)->toBe($expected['tax_total']);
    expect($actualRate->prepayment)->toBe($expected['prepayment']);
    expect($actualRate->balance_due_remaining)->toBe($expected['balance_due_remaining']);
    expect($actualRate->propertyId)->toBe($expected['propertyId']);
    expect($actualRate->propertyIdentifier)->toBe($expected['propertyIdentifier']);
    expect($actualRate->maxPersons)->toBe($expected['maxPersons']);
    expect($actualRate->available)->toBe($expected['available']);
    expect($actualRate->minimalNights)->toBe($expected['minimalNights']);
}