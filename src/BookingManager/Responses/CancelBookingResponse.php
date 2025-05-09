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

        $success = $data['status'] === 'cancelled' || empty($data) || !isset($data['error']);

        return new self(
            $success,
            $success ? 'Booking cancelled successfully.' : 'Failed to cancel booking.'
        );
    }
}
