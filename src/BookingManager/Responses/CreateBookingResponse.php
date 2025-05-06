<?php

namespace Domain\Connections\BookingManager\Responses;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        public readonly int $bookingId,
        public readonly string $identifier,
        public readonly string $message
    ) {}

    /**
     * Maps the raw XML response data to a CreateBookingResponse object.
     * Assumes the input is the parsed content of a successful response.
     * Error handling should occur before calling this map method.
     *
     * @param  Collection  $rawResponse  The raw response data (content of the <booking> tag).
     *
     * @throws Exception If required attributes are missing.
     */
    public static function map(Collection|array $rawResponse): self
    {
        try {
            // Data is expected directly within the <booking> tag passed as $rawResponse
            $attributes = $rawResponse->get('@attributes', []);
            $bookingId = (int) Arr::get($attributes, 'id');
            $identifier = (string) Arr::get($attributes, 'identifier');
            $message = (string) $rawResponse->get('message', 'Booking created.'); // Default message if none provided

            if (! $bookingId || ! $identifier) {
                Log::error('CreateBookingResponse::map - Missing id or identifier attribute', ['response' => $rawResponse]);
                throw new Exception('Invalid response structure: Missing booking id or identifier.');
            }

            return new self(
                bookingId: $bookingId,
                identifier: $identifier,
                message: $message
            );
        } catch (Exception $e) {
            Log::error('Error parsing create booking response', ['error' => $e->getMessage(), 'response' => $rawResponse]);
            // Re-throw or handle as appropriate
            throw new Exception('Failed to map CreateBookingResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
