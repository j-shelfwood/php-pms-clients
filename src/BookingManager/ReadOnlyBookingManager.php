<?php

namespace PhpPms\Clients\BookingManager;

use App\Models\Booking;
use Domain\Dtos\CreateBookingData;
use Domain\Dtos\CreateBookingResponse;
use LogicException;

class ReadOnlyBookingManager extends BookingManager
{
    /**
     * Override createBooking to prevent writes in read-only mode.
     *
     * @throws \LogicException
     */
    public function createBooking(CreateBookingData $bookingData): CreateBookingResponse
    {
        throw new LogicException('Cannot create booking in read-only mode.');
    }

    /**
     * Override finalizeBooking to prevent writes in read-only mode.
     *
     * @throws \LogicException
     */
    public function finalizeBooking(Booking $booking): void
    {
        throw new LogicException('Cannot finalize booking in read-only mode.');
    }
}
