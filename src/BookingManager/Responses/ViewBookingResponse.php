<?php

declare(strict_types=1);

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\BookingDetails;
use Shelfwood\PhpPms\Exceptions\MappingException;

class ViewBookingResponse
{
    /**
     * Represents the response after viewing a booking via the API.
     */
    public function __construct(
        public readonly BookingDetails $booking
    ) {}

    /**
     * Maps the raw XML response data to a ViewBookingResponse object.
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
            throw new MappingException('Failed to map ViewBookingResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}
