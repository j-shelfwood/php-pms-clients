<?php

namespace PMS\BookingManager\Responses;

use SimpleXMLElement;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class PendingBookingResponse
{
    public function __construct(
        public readonly ?BookingStatus $status,
        public readonly ?string $bookingId,
        public readonly ?string $identifier,
        public readonly ?string $message
    ) {}

    public static function map(SimpleXMLElement $xml): self
    {
        $statusValue = (string) $xml->status;
        $errorMessage = isset($xml->error->message) ? (string) $xml->error->message : null;

        $status = match (strtolower($statusValue)) {
            'pending' => BookingStatus::PENDING,
            default => BookingStatus::ERROR, // Default to error if status is not explicitly pending or if there's an error message
        };

        // If there's an error message, status should be ERROR
        if ($errorMessage) {
            $status = BookingStatus::ERROR;
        }

        return new self(
            status: $status,
            bookingId: isset($xml->booking_id) ? (string) $xml->booking_id : null,
            identifier: isset($xml->identifier) ? (string) $xml->identifier : null,
            message: $errorMessage
        );
    }
}
