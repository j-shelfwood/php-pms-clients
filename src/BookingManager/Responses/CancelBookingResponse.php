<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingDetails;
use Shelfwood\PhpPms\Exceptions\MappingException;

class CancelBookingResponse
{
    public function __construct(
        public readonly BookingDetails $booking
    ) {}

    /**
     * Maps the raw XML response data to a CancelBookingResponse object.
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
            throw new MappingException('Failed to map CancelBookingResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}
