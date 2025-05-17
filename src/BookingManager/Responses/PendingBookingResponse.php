<?php

namespace Shelfwood\PhpPms\BookingManager\Responses; // Corrected namespace

use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\BookingManager\Responses\Objects\PendingBooking; // Updated use statement

class PendingBookingResponse
{
    /**
     * @var PendingBooking[]
     */
    public readonly array $pendingBookings;

    public function __construct(array $pendingBookings)
    {
        $this->pendingBookings = $pendingBookings;
    }

    public static function map(array $data): self
    {
        $pendingBookings = [];
        $bookingsDataContainer = $data['bookings'] ?? $data;

        if (empty($bookingsDataContainer)) {
            return new self([]);
        }

        $bookingsList = $bookingsDataContainer['booking'] ?? [];

        if (!empty($bookingsList) && isset($bookingsList['bookingid'])) {
            $bookingsList = [$bookingsList];
        }

        foreach ($bookingsList as $bookingData) {
            if (empty($bookingData) || !isset($bookingData['bookingid'])) {
                continue;
            }
            $pendingBookings[] = new PendingBooking(
                bookingId: (int) ($bookingData['bookingid']),
                status: BookingStatus::tryFrom(strtolower($bookingData['status'] ?? 'pending')) ?? BookingStatus::PENDING,
                guestName: (string) ($bookingData['guestname'] ?? '')
            );
        }
        return new self($pendingBookings);
    }
}
