<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\Objects;

use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class PendingBooking
{
    public function __construct(
        public readonly int $bookingId,
        public readonly BookingStatus $status,
        public readonly string $guestName
    ) {
    }
}
