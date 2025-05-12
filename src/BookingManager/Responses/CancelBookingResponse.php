<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;

use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;

class CancelBookingResponse
{
    public readonly bool $success;
    public readonly ?string $message;
    public readonly BookingStatus $status;

    public function __construct(bool $success, ?string $message = null, BookingStatus $status = BookingStatus::ERROR)
    {
        $this->success = $success;
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @throws MappingException
     */
    public static function map(array $data): self
    {
        if (isset($data['error'])) {
            $message = is_array($data['error']) && isset($data['error']['message']) ? (string)$data['error']['message'] : (string)$data['error'];
            return new self(false, $message, BookingStatus::ERROR);
        }
        $statusString = strtolower((string)($data['status'] ?? ''));
        $message = isset($data['message']) ? (string) $data['message'] : '';
        $success = $statusString === 'cancelled';
        $status = BookingStatus::tryFrom($statusString) ?? BookingStatus::ERROR;
        if ($message === '') {
            $message = $success ? 'Booking cancelled successfully.' : 'Failed to cancel booking.';
        }
        return new self($success, $message, $status);
    }
}
