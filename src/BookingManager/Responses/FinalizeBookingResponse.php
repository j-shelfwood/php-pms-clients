<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Exception;
use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class FinalizeBookingResponse
{
    /**
     * Represents the successful response after finalizing a booking via the API.
     *
     * @param  int  $bookingId  The unique identifier assigned by Booking Manager.
     * @param  string  $identifier  The secondary identifier assigned by Booking Manager.
     * @param  string  $message  A confirmation message from the API.
     */
    public function __construct(
        public readonly int $bookingId,
        public readonly string $identifier,
        public readonly string $message
    ) {}

    /**
     * Maps the raw XML response data to a FinalizeBookingResponse object.
     * Assumes the input is the parsed content of a successful response.
     * Error handling should occur before calling this map method.
     *
     * @param  Collection|array  $rawResponse  The raw response data (content of the <booking> tag).
     *
     * @throws Exception If required attributes are missing.
     */
    public static function map(Collection|array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse instanceof Collection ? $rawResponse : new Collection($rawResponse);
            // Data is expected directly within the <booking> tag passed as $sourceData
            $attributes = new Collection($sourceData->get('@attributes', []));
            $bookingId = (int) $attributes->get('id');
            $identifier = (string) $attributes->get('identifier');
            $message = (string) $sourceData->get('message', 'Booking finalized.'); // Default message if none provided

            if (! $bookingId || ! $identifier) {
                // Removed Log::error
                throw new Exception('Invalid response structure: Missing booking id or identifier.');
            }

            return new self(
                bookingId: $bookingId,
                identifier: $identifier,
                message: $message
            );
        } catch (Exception $e) {
            // Removed Log::error
            // Re-throw or handle as appropriate
            throw new Exception('Failed to map FinalizeBookingResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
