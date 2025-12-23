<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\AgeCategoriesClient;
use Shelfwood\PhpPms\Mews\Payloads\CreateReservationPayload;
use Shelfwood\PhpPms\Mews\Payloads\UpdateReservationPayload;
use Shelfwood\PhpPms\Mews\Responses\ReservationsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Responses\AgeCategoriesResponse;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

class ReservationsClient
{
    private AgeCategoriesClient $ageCategoriesClient;

    public function __construct(
        private MewsHttpClient $httpClient
    ) {
        $this->ageCategoriesClient = new AgeCategoriesClient($httpClient);
    }

    /**
     * Create a new reservation
     *
     * @param CreateReservationPayload $payload Reservation creation payload
     * @param bool $sendConfirmationEmail Whether to send confirmation email
     * @return Reservation Created reservation object
     * @throws MewsApiException
     */
    public function create(
        CreateReservationPayload $payload,
        bool $sendConfirmationEmail = true
    ): Reservation {
        $body = $this->httpClient->buildRequestBody([
            'ServiceId' => $payload->serviceId,
            'Reservations' => [$payload->toArray()],
            'SendConfirmationEmail' => $sendConfirmationEmail,
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/add', $body);

        $reservationsResponse = ReservationsResponse::map($response);

        if ($reservationsResponse->items->isEmpty()) {
            throw new MewsApiException('Failed to create reservation', 500);
        }

        return $reservationsResponse->items->first();
    }

    /**
     * Get reservation by ID
     *
     * @param string $reservationId Reservation UUID
     * @return Reservation Reservation object
     * @throws MewsApiException
     */
    public function getById(string $reservationId): Reservation
    {
        $body = $this->httpClient->buildRequestBody([
            'ReservationIds' => [$reservationId],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/getAll', $body);

        $reservationsResponse = ReservationsResponse::map($response);

        if ($reservationsResponse->items->isEmpty()) {
            throw new MewsApiException("Reservation not found: {$reservationId}", 404);
        }

        return $reservationsResponse->items->first();
    }

    /**
     * Get all reservations for a service within a date range
     *
     * @param string $serviceId Service UUID
     * @param Carbon $startDate Start date (UTC)
     * @param Carbon $endDate End date (UTC)
     * @param array|null $states Filter by reservation states (optional)
     * @return ReservationsResponse Array of reservation objects
     * @throws MewsApiException
     */
    public function getAll(
        string $serviceId,
        Carbon $startDate,
        Carbon $endDate,
        ?array $states = null
    ): ReservationsResponse {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => [$serviceId],
            'FirstTimeUnitStartUtc' => $startDate->toIso8601String(),
            'LastTimeUnitStartUtc' => $endDate->toIso8601String(),
            'ReservationStates' => $states,
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/getAll', $body);

        return ReservationsResponse::map($response);
    }

    /**
     * Update an existing reservation
     *
     * @param UpdateReservationPayload $payload Update payload
     * @return Reservation Updated reservation object
     * @throws MewsApiException
     */
    public function update(UpdateReservationPayload $payload): Reservation
    {
        $body = $this->httpClient->buildRequestBody([
            'ReservationUpdates' => [$payload->toArray()],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/update', $body);

        $reservationsResponse = ReservationsResponse::map($response);

        if ($reservationsResponse->items->isEmpty()) {
            throw new MewsApiException('Failed to update reservation', 500);
        }

        return $reservationsResponse->items->first();
    }

    /**
     * Cancel a reservation
     *
     * @param string $reservationId Reservation UUID
     * @param string $reason Cancellation reason
     * @return Reservation Cancelled reservation object
     * @throws MewsApiException
     */
    public function cancel(string $reservationId, string $reason): Reservation
    {
        $payload = new UpdateReservationPayload(
            reservationId: $reservationId,
            state: ReservationState::Canceled,
            notes: $reason
        );

        return $this->update($payload);
    }

    /**
     * Update reservation state (e.g., Optional → Confirmed)
     *
     * @param string $reservationId Reservation UUID
     * @param ReservationState $newState New state (Optional, Confirmed, Canceled, etc.)
     * @return Reservation Updated reservation object
     * @throws MewsApiException
     */
    public function updateState(string $reservationId, ReservationState $newState): Reservation
    {
        $payload = new UpdateReservationPayload(
            reservationId: $reservationId,
            state: $newState
        );

        return $this->update($payload);
    }

    /**
     * Get age categories for a service
     *
     * @param string $serviceId Service UUID
     * @return AgeCategoriesResponse Age categories response
     * @throws MewsApiException
     */
    public function getAgeCategories(string $serviceId): AgeCategoriesResponse
    {
        return $this->ageCategoriesClient->getAll($serviceId);
    }
}
