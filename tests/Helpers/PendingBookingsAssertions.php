<?php

namespace Tests\Helpers;

use Shelfwood\PhpPms\BookingManager\Responses\PendingBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\Objects\PendingBooking;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

function assertPendingBookingsResponseMatchesExpected(PendingBookingResponse $actualResponse): void
{
    $expected = TestData::getExpectedPendingBookingsData();

    expect($actualResponse->pendingBookings)->toBeArray();
    expect($actualResponse->pendingBookings)->toHaveCount(count($expected['pendingBookings']));

    foreach ($expected['pendingBookings'] as $index => $expectedBooking) {
        $actualBooking = $actualResponse->pendingBookings[$index];

        expect($actualBooking)->toBeInstanceOf(PendingBooking::class);
        expect($actualBooking->bookingId)->toBe($expectedBooking['bookingId']);
        expect($actualBooking->status)->toBeInstanceOf(BookingStatus::class);
        expect($actualBooking->status->value)->toBe($expectedBooking['status']);
        expect($actualBooking->guestName)->toBe($expectedBooking['guestName']);
    }
}