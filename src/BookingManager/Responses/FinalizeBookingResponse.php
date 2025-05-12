<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Exception;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\Exceptions\MappingException;

class FinalizeBookingResponse
{
    /**
     * Represents the response after attempting to finalize a booking via the API.
     *
     * @param int $bookingId The unique identifier assigned by Booking Manager.
     * @param string $identifier The secondary identifier assigned by Booking Manager.
     * @param string $message A confirmation or status message from the API.
     * @param BookingStatus $status The status of the booking finalization.
     */
    public function __construct(
        public readonly int $bookingId,
        public readonly string $identifier,
        public readonly string $message,
        public readonly BookingStatus $status
    ) {}

    /**
     * Maps the raw XML response data to a FinalizeBookingResponse object.
     *
     * @param  array  $rawResponse  The raw response data (content of the <booking> tag or error).
     *
     * @throws Exception If required attributes like booking id or identifier are missing (for non-error states).
     */
    public static function map(array $rawResponse): self
    {
        try {
            // If the root is a <response> with a <booking> child, extract the booking node
            $sourceData = $rawResponse;
            if (isset($sourceData['booking'])) {
                $sourceData = $sourceData['booking'];
            }
            $attributes = $sourceData['@attributes'] ?? [];
            $bookingId = (int) ($attributes['id'] ?? 0);
            $identifier = (string) ($attributes['identifier'] ?? '');
            $message = (string) ($sourceData['message'] ?? '');

            $statusString = isset($sourceData['status']) ? strtolower((string)$sourceData['status']) : null;
            $status = null;

            if ($statusString) {
                $status = BookingStatus::tryFrom($statusString);
                if (!$status) {
                    $status = BookingStatus::ERROR;
                    $message = $message ?: "Received an unrecognized booking status: {$statusString}";
                }
            } elseif ($bookingId && $identifier) {
                $status = BookingStatus::SUCCESS;
                $message = $message ?: 'Booking finalized successfully.';
            } else {
                $status = BookingStatus::ERROR;
                $message = $message ?: 'Invalid response structure: Missing booking id, identifier, or status.';
            }

            if (in_array($status, [BookingStatus::SUCCESS, BookingStatus::OPEN, BookingStatus::PENDING]) && (!$bookingId || !$identifier)) {
                throw new Exception('Invalid response structure: Missing booking id or identifier for a non-error status.');
            }

            return new self(
                bookingId: $bookingId,
                identifier: $identifier ?: '',
                message: $message,
                status: $status
            );
        } catch (Exception $e) {
            throw new MappingException('Failed to map FinalizeBookingResponse: '.$e->getMessage(), 0, $e);
        }
    }
}
