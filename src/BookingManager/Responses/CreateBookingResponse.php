<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingDetails;
use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

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
     * @param  BookingDetails  $booking  The booking details.
     */
    public function __construct(
        public readonly BookingDetails $booking
    ) {}

    /**
     * Maps the raw XML response data to a CreateBookingResponse object.
     *
     * @param array $rawResponse The raw response data from the XMLClient.
     * @throws MappingException If mapping fails.
     */
    public static function map(array $rawResponse): self
    {
        try {
            return new self(
                booking: BookingDetails::map($rawResponse)
            );
        } catch (\Exception $e) {
            throw new MappingException('Failed to map CreateBookingResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}
