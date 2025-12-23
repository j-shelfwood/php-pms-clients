<?php

namespace Shelfwood\PhpPms\BookingManager\Responses; // Corrected namespace

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\BookingManager\Enums\BookingStatus;
use Shelfwood\PhpPms\BookingManager\Responses\Objects\PendingBooking; // Updated use statement

class PendingBookingResponse
{
    /**
     * @param Collection<int, PendingBooking> $pendingBookings
     */
    public function __construct(
        public readonly Collection $pendingBookings
    ) {}

    public static function map(array $data): self
    {
        $bookingsDataContainer = $data['bookings'] ?? $data;

        if (empty($bookingsDataContainer)) {
            return new self(collect());
        }

        $bookingsList = $bookingsDataContainer['booking'] ?? [];

        if (!empty($bookingsList) && isset($bookingsList['bookingid'])) {
            $bookingsList = [$bookingsList];
        }

        $pendingBookings = collect($bookingsList)
            ->filter(fn($bookingData) => !empty($bookingData) && isset($bookingData['bookingid']))
            ->map(fn($bookingData) => new PendingBooking(
                bookingId: (int) ($bookingData['bookingid']),
                status: BookingStatus::tryFrom(strtolower($bookingData['status'] ?? 'pending')) ?? BookingStatus::PENDING,
                guestName: (string) ($bookingData['guestname'] ?? '')
            ));

        return new self($pendingBookings);
    }
}
