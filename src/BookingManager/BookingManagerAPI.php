<?php

namespace Shelfwood\PhpPms\Clients\BookingManager;

use Carbon\Carbon;
use Tightenco\Collect\Support\Collection;
use Shelfwood\PhpPms\Clients\XMLClient;
use Shelfwood\PhpPms\Clients\BookingManager\Payloads\CreateBookingPayload;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\RateResponse;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\PropertyInfoResponse;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\CreateBookingResponse;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\FinalizeBookingResponse;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\CalendarChangesResponse;

class BookingManagerAPI extends XMLClient
{
    public function getRateForStay(int $id, Carbon $arrival, Carbon $departure): RateResponse
    {
        $response = $this->makeRequest('info.xml', [
            'id' => $id,
            'arrival' => $arrival->format('Y-m-d'),
            'departure' => $departure->format('Y-m-d'),
        ]);

        return RateResponse::map($response);
    }

    /**
     * Get all properties from BookingManager
     *
     * @return Collection<PropertyInfoResponse>
     */
    public function getAllProperties(): Collection
    {
        return $this->makeRequest('details.xml')
            ->map(fn (Collection $details) => PropertyInfoResponse::map($details));
    }

    public function getPropertyById(int $id): PropertyInfoResponse
    {
        return PropertyInfoResponse::map($this->makeRequest('details.xml', ['id' => $id]));
    }

    public function getCalendarForDateRange(int $propertyId, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        return CalendarResponse::map($this->makeRequest('calendar.xml', [
            'id' => $propertyId,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ]));
    }

    public function getCalendarChanges(Carbon $since): CalendarChangesResponse
    {
        return CalendarChangesResponse::map($this->makeRequest('calendar_changes.xml', [
            'time' => $since->format('Y-m-d H:i:s'),
        ]));
    }

    public function createBooking(CreateBookingPayload $bookingData): CreateBookingResponse
    {
        $response = $this->makeRequest('booking_create.xml?overwrite_rates=1', $bookingData->toArray(), 'POST');

        return CreateBookingResponse::map($response);
    }

    public function finalizeBooking(int $externalBookingId): FinalizeBookingResponse
    {
        $response = $this->makeRequest('booking_finalize.xml', [
            'id' => $externalBookingId,
            'overwrite_rates' => 1,
        ]);

        return FinalizeBookingResponse::map($response);
    }
}
