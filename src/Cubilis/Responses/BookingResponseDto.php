<?php

namespace Domain\Connections\Cubilis\Dtos;

use Domain\Dtos\CreateBookingResponse;
use Illuminate\Support\Collection;

class BookingResponseDto
{
    private Collection $raw;

    private function __construct(Collection $raw)
    {
        $this->raw = $raw;
    }

    public static function fromCollection(Collection $raw): self
    {
        return new self($raw);
    }

    public function toDomain(): CreateBookingResponse
    {
        $root = $this->raw;
        // In OTA_HotelResRS, success only indicates no error; no identifier provided.
        // If there is a ReservationID element, use it.
        $attrs = $root->get('@attributes', []);
        $bookingId = $attrs['BookingID'] ?? '';
        $identifier = $attrs['ResID'] ?? $bookingId;

        return new CreateBookingResponse(
            response: $this->raw,
            booking_id: $bookingId,
            identifier: $identifier
        );
    }
}
