<?php

namespace Shelfwood\PhpPms\BookingManager\Payloads;

/**
 * Represents the data necessary to edit a booking via the BookingManager API.
 * Only fields to be updated need to be set; booking id is required.
 * Based on the edit-booking.xml API documentation.
 */
class EditBookingPayload
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $end = null,
        public readonly ?string $name_first = null,
        public readonly ?string $name_last = null,
        public readonly ?string $email = null,
        public readonly ?string $address_1 = null,
        public readonly ?string $address_2 = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $phone = null,
        public readonly ?int $amount_adults = null,
        public readonly ?int $amount_childs = null,
        public readonly ?string $time_arrival = null,
        public readonly ?string $flight = null,
        public readonly ?string $notes = null
    ) {
    }

    /**
     * Converts the payload object to an array, filtering out null values.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
