<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Exception;

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
     * @param  array  $rawResponse  The raw response data (content of the <booking> tag).
     *
     * @throws Exception If required attributes are missing.
     */
    public static function map(array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse;
            $attributes = $sourceData['@attributes'] ?? [];
            $bookingId = (int) ($attributes['id'] ?? 0);
            $identifier = (string) ($attributes['identifier'] ?? '');
            $message = (string) ($sourceData['message'] ?? 'Booking finalized.');

            if (! $bookingId || ! $identifier) {
                throw new Exception('Invalid response structure: Missing booking id or identifier.');
            }

            return new self(
                bookingId: $bookingId,
                identifier: $identifier,
                message: $message
            );
        } catch (Exception $e) {
            throw new Exception('Failed to map FinalizeBookingResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
