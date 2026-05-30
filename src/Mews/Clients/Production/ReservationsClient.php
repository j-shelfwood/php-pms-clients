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
            'Extent' => [
                'Reservations' => true,
            ],
            'Limitation' => [
                'Count' => 1,
            ],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/getAll/2023-06-06', $body);

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
        $allReservations = [];
        $cursor = null;

        do {
            $body = $this->httpClient->buildRequestBody([
                'ServiceIds' => [$serviceId],
                'Extent' => [
                    'Reservations' => true,
                ],
                'CollidingUtc' => [
                    'StartUtc' => $startDate->copy()->utc()->toIso8601ZuluString(),
                    'EndUtc' => $endDate->copy()->utc()->toIso8601ZuluString(),
                ],
                'States' => $states,
                'Limitation' => [
                    'Count' => 1000,
                    ...($cursor !== null ? ['Cursor' => $cursor] : []),
                ],
            ]);

            $response = $this->httpClient->post('/api/connector/v1/reservations/getAll/2023-06-06', $body);

            $pageResponse = ReservationsResponse::map($response);
            $allReservations = array_merge($allReservations, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new ReservationsResponse(items: collect($allReservations));
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
        // Mews dedicated cancellation endpoint. Sending State=Canceled through
        // /reservations/update returns 200 but the state silently does NOT
        // transition — Mews accepts the update, advances UpdatedUtc, and leaves
        // State at its prior value. Verified against Mews production 2026-05-28.
        // The correct path per Mews Connector docs is /reservations/cancel
        // with `ReservationIds` (array) + `Notes`.
        $body = $this->httpClient->buildRequestBody([
            'ReservationIds' => [$reservationId],
            'Notes' => $reason,
            'PostCancellationFee' => false,
            'SendEmail' => false,
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/cancel', $body);

        // /reservations/cancel returns {"ReservationIds": [...]} — it doesn't
        // echo the full reservation object. Re-fetch to return a Reservation VO
        // so the existing public contract (Reservation return type) is preserved.
        if (empty($response['ReservationIds'] ?? [])) {
            throw new MewsApiException('Failed to cancel reservation', 500);
        }

        return $this->getById($reservationId);
    }

    /**
     * Confirm a previously-Optional reservation.
     *
     * Mews has a dedicated state-transition endpoint for promoting reservations
     * out of Optional. Sending State=Confirmed through /reservations/update
     * returns 200 but silently does NOT transition state — Mews accepts the
     * update, advances UpdatedUtc, and leaves State at Optional. Verified
     * empirically against Mews production 2026-05-30 (probe reservation
     * 91277ea1-e6d3-4bdb-a658-b45a0143d1c6: update→200+no change, confirm→200+Confirmed).
     *
     * Mews rejects duplicate confirmation with 403 "Cannot confirm order".
     * Mews rejects confirmation of cancelled reservations with the same 403.
     * Callers MUST check current state via getById before invoking — there is
     * no idempotency at the API layer.
     *
     * @param string $reservationId Reservation UUID (must be in Optional state)
     * @return Reservation Confirmed reservation object (re-fetched)
     * @throws MewsApiException On 403 (already-confirmed, cancelled, or other policy block)
     */
    public function confirm(string $reservationId): Reservation
    {
        $body = $this->httpClient->buildRequestBody([
            'ReservationIds' => [$reservationId],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/reservations/confirm', $body);

        // /reservations/confirm returns {"ReservationIds": [...]} — no full
        // reservation object. Re-fetch to honour the Reservation return type.
        if (empty($response['ReservationIds'] ?? [])) {
            throw new MewsApiException('Failed to confirm reservation', 500);
        }

        return $this->getById($reservationId);
    }

    /**
     * Update reservation state (e.g., Optional → Confirmed).
     *
     * @deprecated Use confirm() or cancel() for state transitions.
     *             /reservations/update silently ignores the State field — verified
     *             against Mews production 2026-05-30. This method is retained for
     *             backwards compatibility but does NOT change reservation state.
     *
     * @param string $reservationId Reservation UUID
     * @param ReservationState $newState Ignored by Mews when sent via this path
     * @return Reservation Reservation as-is (state unchanged)
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
