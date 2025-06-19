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
                throw new ApiException($errorDetails->message, $errorDetails->code ?? 'UNKNOWN', null, $errorDetails);
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
        // Use calendar.xml endpoint to get full calendar data with rates
        $apiParams = [
            'id' => $propertyId,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ];

        $parsedData = $this->performApiCall('calendar', $apiParams);

        // CalendarResponse::map() handles calendar.xml format when no startDate/endDate are provided
        return CalendarResponse::map($parsedData);
    }

    /**
     * Get availability-only data for a property within a date range.
     * This is faster than calendar() but doesn't include rate information.
     * Use this when you only need to check availability, not pricing.
     *
     * @param int $propertyId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return CalendarResponse
     */
    public function availability(int $propertyId, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        // Use availability.xml endpoint for fast availability-only data
        $apiParams = [
            'id' => $propertyId,
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
        ];

        $parsedData = $this->performApiCall('availability', $apiParams);

        // CalendarResponse::map() handles availability.xml format when startDate/endDate are provided
        return CalendarResponse::map($parsedData, $startDate, $endDate);
    }

    public function calendarChanges(Carbon $since): CalendarChangesResponse
    {
        $apiParams = [
            'time' => $since->format('Y-m-d H:i:s'),
        ];
        $parsedData = $this->performApiCall('changes', $apiParams);

        // The changes.xml endpoint returns <changes> root with <change> elements
        return CalendarChangesResponse::map($parsedData);
    }

    public function rateForStay(int $propertyId, Carbon $arrival, Carbon $departure, int $adults): RateResponse
    {
        $apiParams = [
            'id' => $propertyId,
            'arrival' => $arrival->format('Y-m-d'),
            'departure' => $departure->format('Y-m-d'),
            'adults' => $adults,
        ];
        $parsedData = $this->performApiCall('info.xml', $apiParams);

        // Handle both API response structures:
        // Mock/Test: parsedData['info']['property']['rate']
        // Live API: parsedData['property']['rate']
        $hasRate = isset($parsedData['info']['property']['rate']) || isset($parsedData['property']['rate']);

        if (!$parsedData || !$hasRate) {
            throw new MappingException('Invalid response structure for rate for stay: missing rate data.');
        }

        return RateResponse::map($parsedData);
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
