<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;

/**
 * @phpstan-type BookingDetails array{
 *   id: string,
 *   status: string,
 *   arrival: string,
 *   departure: string,
 *   totalPrice: string,
 *   currency: string,
 *   guestName: string,
 *   guestEmail: string,
 *   guestPhone: string,
 *   adults: string,
 *   children: string,
 *   notes: string,
 *   propertyId: string,
 *   roomId: string,
 *   rateId: string
 * }
 */
class CreateBookingResponse
{
    /**
     * Represents the successful response after creating a booking via the API.
     *
     * @param  int  $bookingId  The unique identifier assigned by Booking Manager.
     * @param  string  $identifier  The secondary identifier assigned by Booking Manager.
     * @param  string  $message  A confirmation message from the API.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $arrival,
        public readonly string $departure,
        public readonly string $totalPrice,
        public readonly string $currency,
        public readonly string $guestName,
        public readonly string $guestEmail,
        public readonly string $guestPhone,
        public readonly string $adults,
        public readonly string $children,
        public readonly string $notes,
        public readonly string $propertyId,
        public readonly string $roomId,
        public readonly string $rateId
    ) {}

    /**
     * Maps the raw response data to a CreateBookingResponse object.
     *
     * @param  array  $rawResponse  The raw response data (content of the <booking> tag).
     * @return self The mapped CreateBookingResponse object.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse;
            $attributes = $sourceData['@attributes'] ?? [];

            return new self(
                id: $attributes['id'] ?? '',
                status: $attributes['status'] ?? '',
                arrival: $sourceData['arrival'] ?? '',
                departure: $sourceData['departure'] ?? '',
                totalPrice: $sourceData['totalPrice'] ?? '',
                currency: $sourceData['currency'] ?? '',
                guestName: $sourceData['guestName'] ?? '',
                guestEmail: $sourceData['guestEmail'] ?? '',
                guestPhone: $sourceData['guestPhone'] ?? '',
                adults: $sourceData['adults'] ?? '',
                children: $sourceData['children'] ?? '',
                notes: $sourceData['notes'] ?? '',
                propertyId: $sourceData['propertyId'] ?? '',
                roomId: $sourceData['roomId'] ?? '',
                rateId: $sourceData['rateId'] ?? ''
            );
        } catch (\Throwable $e) {
            throw new MappingException('Error mapping CreateBookingResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
