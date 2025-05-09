<?php

namespace Shelfwood\PhpPms\BookingManager\Responses;

use Shelfwood\PhpPms\Exceptions\MappingException;

class CancelBookingResponse
{
    public bool $success;

    public ?string $message;

    public function __construct(bool $success, ?string $message = null)
    {
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * @throws MappingException
     */
    public static function map(array $data): self
    {
        if (isset($data['error'])) {
            return new self(false, (string) $data['error']);
        }
        $status = (string) ($data['status'] ?? '');
        $message = isset($data['message']) ? (string) $data['message'] : '';
        $success = $status === 'cancelled';
        if ($message === '') {
            $message = $success ? 'Booking cancelled successfully.' : 'Failed to cancel booking.';
        }
        return new self($success, $message);
    }
}
