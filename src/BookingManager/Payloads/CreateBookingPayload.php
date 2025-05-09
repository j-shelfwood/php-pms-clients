<?php

namespace Shelfwood\PhpPms\BookingManager\Payloads;

class CreateBookingPayload
{
    public const RATE_INCL = 1;
    public const RATE_EXCL = 0;

    /**
     * Represents the data necessary to create a booking via the BookingManager API.
     * This includes personal information, booking details, property information, and optional rate details.
     * Based on the create-booking.xml API documentation.
     */
    public function __construct(
        public readonly int $property_id,
        public readonly string $start,
        public readonly string $end,
        public readonly string $name_first,
        public readonly string $name_last,
        public readonly string $email,
        public readonly string $address_1,
        public readonly string $city,
        public readonly string $country,
        public readonly string $phone,
        public readonly int $amount_adults,
        public readonly ?string $address_2 = null,
        public readonly ?int $amount_childs = null,
        public readonly ?string $time_arrival = null,
        public readonly ?string $flight = null,
        public readonly ?string $notes = null,
        public readonly ?float $rate_final = null,
        public readonly ?int $rate_incl = null,
        public readonly ?float $rate_prepayment = null,
        public readonly ?float $balance_due = null
    ) {
        //
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
