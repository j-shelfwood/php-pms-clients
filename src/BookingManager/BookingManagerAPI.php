<?php

namespace PhpPms\Clients\BookingManager;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Domain\Connections\XMLClient;
use Domain\Dtos\CreateBookingData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Domain\Dtos\CalendarChangesResponse;
use Domain\Connections\BookingManager\Responses\RateResponse;
use Domain\Connections\BookingManager\Responses\CalendarResponse;
use Domain\Connections\BookingManager\Responses\PropertyInfoResponse;
use Domain\Connections\BookingManager\Responses\CreateBookingResponse;
use Domain\Connections\BookingManager\Responses\FinalizeBookingResponse;

class BookingManagerAPI extends XMLClient
{
    public function getRateForStay(int $id, Carbon $arrival, Carbon $departure): RateResponse
    {
        $response = $this->makeRequest('info.xml', [
            'id' => $id,
            'arrival' => $arrival->format('Y-m-d'),
            'departure' => $departure->format('Y-m-d'),
        ]);

        // Map raw response into domain BookingRate
        return RateResponse::map($response);
    }

    /**
     * Get all properties from BookingManager
     *
     * @return Collection<DetailsResponse>
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

    public function createBooking(CreateBookingData $bookingData): CreateBookingResponse
    {
        $requestData = $bookingData->toArray();

        Log::channel('sync')->debug('Creating booking with rate data via API', [
            'rate_final' => $requestData['rate_final'] ?? null,
            'rate_incl' => $requestData['rate_incl'] ?? null,
            'rate_prepayment' => $requestData['rate_prepayment'] ?? null,
            'discount_applied' => config('instance.discount', 0),
            'property_id' => $bookingData->property_id,
        ]);

        // Add tourist tax note for non-percentage tax types
        $taxService = app(\App\Services\TaxService::class);

        if (! $taxService->isTouristTaxPercentage()) {
            $requestData = $this->addTouristTaxNote($requestData, $taxService);
        }

        $response = $this->makeRequest('booking_create.xml?overwrite_rates=1', $bookingData->toArray(), 'POST');

        return CreateBookingResponse::map($response);
    }

    /**
     * Add a note about the tourist tax to the booking request data.
     */
    private function addTouristTaxNote(array $requestData, \App\Services\TaxService $taxService): array
    {
        $notes = $requestData['notes'] ?? '';

        // Check if a tourist tax note already exists
        if (str_contains($notes, 'Tourist tax') || str_contains($notes, 'tourist tax')) {
            return $requestData;
        }

        // Calculate the total tourist tax amount
        $persons = $requestData['amount_adults'] + ($requestData['amount_childs'] ?? 0);
        $arrival = Carbon::parse($requestData['start']);
        $departure = Carbon::parse($requestData['end']);
        $nights = $arrival->diffInDays($departure);
        $ratePerNight = $taxService->getTouristTaxValue();
        $totalTax = $ratePerNight * $persons * $nights;

        $enhancedMessage = __('IMPORTANT: Tourist tax of €:amount (€:rate per person per night × :persons persons × :nights nights) will be collected upon arrival.', [
            'amount' => number_format($totalTax, 2),
            'rate' => number_format($ratePerNight, 2),
            'persons' => $persons,
            'nights' => $nights,
        ]);

        $requestData['notes'] = trim($notes."\n\n".$enhancedMessage);

        return $requestData;
    }

    public function finalizeBooking(Booking $booking): FinalizeBookingResponse
    {
        $response = $this->makeRequest('booking_finalize.xml', [
            'id' => $booking->external_id,
            'overwrite_rates' => 1,
        ]);

        Log::channel('sync')->info('Booking finalized successfully via API', [
            'booking_id' => $booking->id,
            'response' => $response,
        ]);

        return FinalizeBookingResponse::map($response);
    }
}
