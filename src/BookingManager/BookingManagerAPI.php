<?php

namespace Shelfwood\PhpPms\BookingManager;

use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;
use Shelfwood\PhpPms\Http\XMLClient;
use Shelfwood\PhpPms\Http\XMLParser;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Payloads\CreateBookingPayload;
use Shelfwood\PhpPms\BookingManager\Payloads\EditBookingPayload;
use Shelfwood\PhpPms\BookingManager\Responses\CancelBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CreateBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\FinalizeBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ViewBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\EditBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PendingBookingResponse;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPIException;
use Shelfwood\PhpPms\Exceptions\NetworkException;
use Shelfwood\PhpPms\Exceptions\XmlParsingException;
use Shelfwood\PhpPms\Exceptions\ApiException;
use Shelfwood\PhpPms\Exceptions\MappingException;
use GuzzleHttp\Exception\ClientException;

class BookingManagerAPI extends XMLClient
{
    public function __construct(
        ClientInterface $httpClient,
        string $apiKey,
        string $baseUrl,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($baseUrl, $apiKey, $httpClient, $logger);
    }

    /**
     * Edits a booking using the provided payload.
     *
     * @param EditBookingPayload $payload
     * @return EditBookingResponse
     * @throws ApiException|MappingException|NetworkException|XmlParsingException
     */
    public function editBooking(EditBookingPayload $payload): EditBookingResponse
    {
        $apiParams = $payload->toArray();
        $parsedData = $this->performApiCall('booking_edit', $apiParams);
        return EditBookingResponse::map($parsedData);
    }

    /**
     * Returns pending bookings for a given booking id.
     *
     * @param int $bookingId
     * @return PendingBookingResponse
     * @throws ApiException|MappingException|NetworkException|XmlParsingException
     */
    public function pendingBookings(int $bookingId): PendingBookingResponse
    {
        $apiParams = [
            'bookingid' => $bookingId,
        ];
        $parsedData = $this->performApiCall('booking_pending', $apiParams);
        return PendingBookingResponse::map($parsedData);
    }

    /**
     * Centralized API call handler for BookingManager endpoints.
     * Handles network, parsing, and API errors, throws exceptions on error.
     */
    protected function performApiCall(string $endpoint, array $apiParams = [], int $attempt = 1): array
    {
        $url = $this->getEndpointUrl($endpoint);
        $formData = $apiParams; // No 'request' parameter needed for XML endpoints

        try {
            $xmlBody = $this->executePostRequest($url, $formData);
            $parsedData = XMLParser::parse($xmlBody);

            if (XMLParser::hasError($parsedData)) {
                $errorDetails = XMLParser::extractErrorDetails($parsedData);
                throw new ApiException($errorDetails->message, $errorDetails->code ?? 0, null, $errorDetails);
            }

            return $parsedData;
        } catch (NetworkException | XmlParsingException | ApiException $e) {
            // Handle rate limiting with progressive backoff
            if ($this->isRateLimitError($e) && $attempt <= 5) {
                $this->handleRateLimit($e, $attempt);
                return $this->performApiCall($endpoint, $apiParams, $attempt + 1);
            }

            $this->logger->error("API call failed: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'params' => $apiParams,
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'attempt' => $attempt,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Unexpected error during API call: {$e->getMessage()}", [
                'endpoint' => $endpoint,
                'params' => $apiParams,
                'exception' => get_class($e),
                'attempt' => $attempt,
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Shelfwood\PhpPms\Exceptions\PmsClientException("Unexpected error: " . $e->getMessage(), 0, $e);
        }
    }

    private function getEndpointUrl(string $endpoint): string
    {
        return "{$this->baseUrl}/{$endpoint}.xml";
    }

    private function getEndpoint(): string
    {
        return "{$this->baseUrl}/api";
    }

    private function handleRateLimit(\Throwable $e, int $attempt = 1): void
    {
        if ($this->isRateLimitError($e)) {
            $backoffSeconds = min(60 * pow(2, $attempt - 1), 600); // 60s, 120s, 240s, 480s, 600s max
            $this->logger->warning("Rate limit hit, backing off for {$backoffSeconds}s", [
                'attempt' => $attempt,
                'error' => $e->getMessage()
            ]);
            sleep($backoffSeconds);
        }
    }

    private function isRateLimitError(\Throwable $e): bool
    {
        return ($e instanceof ClientException && $e->getResponse() && $e->getResponse()->getStatusCode() === 403) ||
               ($e instanceof ApiException && $e->getCode() === 403);
    }

    /**
     * Get all properties from BookingManager with comprehensive details.
     *
     * This method calls the details.xml endpoint to get full property information
     * including all amenities, services, costs, taxes, and other detailed fields.
     *
     * @return PropertiesResponse
     */
    public function properties(): PropertiesResponse
    {
        $parsedArray = $this->performApiCall('details');
        if (!$parsedArray || !isset($parsedArray['property'])) {
            $this->logger->warning('No <property> elements found directly under the root parsed XML for getAllProperties.', [
                'parsed_xml_keys' => is_array($parsedArray) ? implode(',', array_keys($parsedArray)) : 'null',
            ]);
            return new PropertiesResponse(properties: []);
        }
        if (isset($parsedArray['property']) && isset($parsedArray['property']['@attributes'])) {
            $parsedArray['property'] = [$parsedArray['property']];
        }
        return PropertiesResponse::map($parsedArray);
    }

    public function property(int $id): PropertyResponse
    {
        $apiParams = ['id' => $id];
        $parsedData = $this->performApiCall('details', $apiParams);
        if (!isset($parsedData['property'])) {
            throw new MappingException('Invalid response structure for property: missing "property" key.');
        }
        return PropertyResponse::map($parsedData['property']);
    }

    public function calendar(int $propertyId, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        $days = [];
        $current = $startDate->copy();

        // Fetch rate and availability for each day in the range
        while ($current->lte($endDate)) {
            try {
                // Use info.xml endpoint to get rates for each day
                $rateResponse = $this->rateForStay(
                    $propertyId,
                    $current,
                    $current->copy()->addDay(),
                    1 // Default to 1 adult for availability check
                );

                $days[] = new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo(
                    day: $current->copy(),
                    season: null, // info.xml does not provide season information
                    modified: Carbon::now(),
                    available: $rateResponse->available ? 1 : 0,
                    stayMinimum: $rateResponse->minimalNights ?? 1,
                    rate: new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarRate(
                        percentage: 0.0, // Not provided by info.xml
                        currency: 'EUR', // Default currency
                        total: $rateResponse->final_before_taxes ?? 0.0,
                        final: $rateResponse->final_before_taxes ?? 0.0,
                        tax: new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarTax(
                            total: $rateResponse->tax_total ?? 0.0,
                            other: $rateResponse->tax_other ?? 0.0,
                            otherType: '', // Not provided by info.xml
                            otherValue: 0.0, // Not provided by info.xml
                            vat: $rateResponse->tax_vat ?? 0.0,
                            vatValue: 0.0, // Not provided by info.xml
                            final: $rateResponse->final_after_taxes ?? 0.0
                        ),
                        fee: 0.0, // Not provided by info.xml
                        prepayment: $rateResponse->prepayment ?? 0.0,
                        balanceDue: $rateResponse->balance_due_remaining ?? 0.0
                    ),
                    maxStay: $rateResponse->maxPersons ?? null,
                    closedOnArrival: !$rateResponse->available,
                    closedOnDeparture: !$rateResponse->available,
                    stopSell: !$rateResponse->available
                );
            } catch (\Exception $e) {
                // Log the error for a specific day but continue the loop
                $this->logger?->error("Failed to fetch calendar data for property {$propertyId} on {$current->toDateString()}", [
                    'error' => $e->getMessage(),
                    'property_id' => $propertyId,
                    'date' => $current->toDateString()
                ]);

                // Create a default unavailable day entry
                $days[] = new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo(
                    day: $current->copy(),
                    season: null,
                    modified: Carbon::now(),
                    available: 0,
                    stayMinimum: 1,
                    rate: new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarRate(
                        percentage: 0.0,
                        currency: 'EUR',
                        total: 0.0,
                        final: 0.0,
                        tax: new \Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarTax(
                            total: 0.0,
                            other: 0.0,
                            otherType: '',
                            otherValue: 0.0,
                            vat: 0.0,
                            vatValue: 0.0,
                            final: 0.0
                        ),
                        fee: 0.0,
                        prepayment: 0.0,
                        balanceDue: 0.0
                    ),
                    maxStay: null,
                    closedOnArrival: true,
                    closedOnDeparture: true,
                    stopSell: true
                );
            }

            $current->addDay();
        }

        return new CalendarResponse($propertyId, $days);
    }

    public function calendarChanges(Carbon $since): CalendarChangesResponse
    {
        $apiParams = [
            'time' => $since->format('Y-m-d H:i:s'),
        ];
        $parsedData = $this->performApiCall('changes', $apiParams);
        if (!$parsedData || !isset($parsedData['property'])) {
            $parsedData['property'] = [];
        }
        if (isset($parsedData['property']) && !is_array($parsedData['property'])) {
            $parsedData['property'] = [$parsedData['property']];
        } elseif (isset($parsedData['property']) && isset($parsedData['property']['@attributes'])){
            $parsedData['property'] = [$parsedData['property']];
        }
        return CalendarChangesResponse::map($parsedData);
    }

    public function rateForStay(int $propertyId, Carbon $arrivalDate, Carbon $departureDate, int $numAdults, ?int $numChildren = null, ?int $numBabies = null): RateResponse
    {
        $apiParams = [
            'id' => $propertyId,
            'arrival' => $arrivalDate->format('Y-m-d'),
            'departure' => $departureDate->format('Y-m-d'),
            'guests' => $numAdults + ($numChildren ?? 0) + ($numBabies ?? 0),
        ];

        $parsedData = $this->performApiCall('info', $apiParams);

        if (!$parsedData || !isset($parsedData['rate'])) {
            throw new MappingException('Invalid response structure for rate for stay: missing "rate" key.');
        }

        return RateResponse::map($parsedData['rate']);
    }

    public function createBooking(CreateBookingPayload $payload): CreateBookingResponse
    {
        $apiParams = $payload->toArray();
        $parsedData = $this->performApiCall('booking_create', $apiParams);
        return CreateBookingResponse::map($parsedData);
    }

    public function finalizeBooking(int $externalBookingId): FinalizeBookingResponse
    {
        $apiParams = [
            'booking_id' => $externalBookingId,
            'overwrite_rates' => 1,
        ];
        $parsedData = $this->performApiCall('booking_finalize', $apiParams);
        return FinalizeBookingResponse::map($parsedData);
    }

    public function cancelBooking(int $bookingId, string $reason): CancelBookingResponse
    {
        $apiParams = [
            'booking_id' => $bookingId,
            'reason' => $reason,
        ];
        $parsedData = $this->performApiCall('booking_cancel', $apiParams);
        return CancelBookingResponse::map($parsedData);
    }

    public function viewBooking(int $bookingId): ViewBookingResponse
    {
        $apiParams = [
            'bookingid' => $bookingId,
        ];
        $parsedData = $this->performApiCall('booking_view', $apiParams);
        return ViewBookingResponse::map($parsedData);
    }
}
