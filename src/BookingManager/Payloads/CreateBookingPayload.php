<?php

namespace PhpPms\Clients\BookingManager\Payloads;

use App\Models\Booking;
use App\Models\Property;

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
        // Required API parameters
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

        // Optional API parameters
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

    public static function map(Booking $booking): self
    {
        $externalPropertyId = Property::externalIdFor($booking->property_id);
        if (! $externalPropertyId) {
            throw new \InvalidArgumentException("Could not find external ID for property ID: {$booking->property_id}");
        }

        return new self(
            property_id: $externalPropertyId,
            start: $booking->arrival->format('Y-m-d'),
            end: $booking->departure->format('Y-m-d'),
            name_first: $booking->first_name,
            name_last: $booking->last_name,
            email: $booking->email,
            address_1: $booking->address_1,
            city: $booking->city,
            country: $booking->country,
            phone: $booking->phone,
            amount_adults: $booking->amount_adults,
            address_2: $booking->address_2,
            amount_childs: $booking->amount_children > 0 ? $booking->amount_children : null,
            time_arrival: $booking->time_arrival,
            flight: $booking->flight,
            notes: $booking->notes,
            rate_final: $booking->rate_meta['final_before_taxes'] ?? null,
            rate_incl: isset($booking->rate_meta['final_before_taxes']) ? self::RATE_EXCL : null,
            rate_prepayment: $booking->rate_meta['prepayment'] ?? null,
            balance_due: $booking->balance_due
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
