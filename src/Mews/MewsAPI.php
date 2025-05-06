<?php

namespace Domain\Connections\Mews;

use App\Models\Booking;
use App\Models\Property;
use Domain\Connections\Mews\Dtos\CalendarChangesResponseDto;
use Domain\Connections\Mews\Dtos\PropertyListResponseDto;
use Domain\Connections\Mews\Dtos\RateResponseDto;
use Domain\Dtos\BookingRate;
use Domain\Dtos\CalendarChangesResponse;
use Domain\Dtos\CalendarListResponse;
use Domain\Dtos\CreateBookingData;
use Domain\Dtos\CreateBookingResponse;
use Domain\Dtos\PropertyListResponse;
use Domain\Interfaces\BookingSystemAPI;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MewsAPI implements BookingSystemAPI
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * Send HTTP request to Mews API and return decoded JSON.
     */
    protected function sendRequest(string $method, string $uri, array $data = []): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->{$method}("{$this->baseUrl}/{$uri}", $data);
            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Mews API error', ['method' => $method, 'uri' => $uri, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getAllProperties(): PropertyListResponse
    {
        $raw = $this->sendRequest('get', 'bookable-objects');
        $dto = PropertyListResponseDto::fromArray($raw);

        return $dto->toDomain();
    }

    public function getRateForStay(int $externalId, Carbon $arrival, Carbon $departure): BookingRate
    {
        $raw = $this->sendRequest('get', 'rates', [
            'externalPropertyId' => $externalId,
            'from' => $arrival->toDateString(),
            'to' => $departure->toDateString(),
        ]);
        $dto = RateResponseDto::fromArray($raw);

        return $dto->toDomain();
    }

    public function getCalendarChanges(Carbon $since): CalendarChangesResponse
    {
        $raw = $this->sendRequest('get', 'availability/changes', ['since' => $since->toIso8601String()]);
        $dto = CalendarChangesResponseDto::fromArray($raw);

        return $dto->toDomain();
    }

    public function getCalendarForDateRange(Property $property, Carbon $start, Carbon $end): CalendarListResponse
    {
        $raw = $this->sendRequest('get', "availability/{$property->external_id}", [
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
        ]);

        // TODO: map $raw to calendar list
        return new CalendarListResponse(collect());
    }

    public function createBooking(CreateBookingData $data): CreateBookingResponse
    {
        $raw = $this->sendRequest('post', 'bookings', $data->toArray());

        // TODO: extract booking identifiers
        return new CreateBookingResponse(
            response: collect($raw),
            booking_id: $raw['id'] ?? '',
            identifier: $raw['identifier'] ?? ''
        );
    }

    public function finalizeBooking(Booking $booking): void
    {
        $this->sendRequest('post', "bookings/{$booking->external_id}/finalize");
        Log::info('Mews finalizeBooking called', ['booking_id' => $booking->id]);
    }
}
