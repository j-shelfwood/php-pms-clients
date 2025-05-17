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

class BookingManagerAPI extends XMLClient
{
    public function __construct(
        ClientInterface $httpClient,
        string $apiKey,
        string $username,
        string $baseUrl,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($baseUrl, $apiKey, $username, $httpClient, $logger);
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
        $parsedData = $this->performApiCall('BEXML', 'edit_booking', $apiParams);
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
        $parsedData = $this->performApiCall('BEXML', 'pending_bookings', $apiParams);
        return PendingBookingResponse::map($parsedData);
    }

    /**
     * Centralized API call handler for BookingManager endpoints.
     * Handles network, parsing, and API errors, throws exceptions on error.
     */
    protected function performApiCall(string $endpointName, string $requestCommand, array $apiParams = []): array
    {
        $url = $this->getEndpoint($endpointName);
        $formData = array_merge(['request' => $requestCommand], $apiParams);
        try {
            $xmlBody = $this->executePostRequest($url, $formData);
            $parsedData = XMLParser::parse($xmlBody);
            if (XMLParser::hasError($parsedData)) {
                $errorDetails = XMLParser::extractErrorDetails($parsedData);
                throw new ApiException($errorDetails->message, $errorDetails->code ?? 0, null, $errorDetails);
            }
            return $parsedData;
        } catch (NetworkException | XmlParsingException | ApiException $e) {
            $this->logger->error("API call failed: {$e->getMessage()}", [
                'endpoint' => $endpointName,
                'request' => $requestCommand,
                'params' => $apiParams,
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error("Unexpected error during API call: {$e->getMessage()}", [
                'endpoint' => $endpointName,
                'request' => $requestCommand,
                'params' => $apiParams,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Shelfwood\PhpPms\Exceptions\PmsClientException("Unexpected error: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get all properties from BookingManager
     *
     * @return PropertiesResponse
     */
    public function properties(): PropertiesResponse
    {
        $parsedArray = $this->performApiCall('BEXML', 'list_properties');
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
        $parsedData = $this->performApiCall('BEXML', 'list_property', $apiParams);
        if (!isset($parsedData['property'])) {
            throw new MappingException('Invalid response structure for property: missing "property" key.');
        }
        return PropertyResponse::map($parsedData['property']);
    }

    public function calendar(int $propertyId, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        $apiParams = [
            'property_id' => $propertyId,
            'date_from' => $startDate->format('Ymd'),
            'date_to' => $endDate->format('Ymd'),
        ];
        $parsedData = $this->performApiCall('BEXML', 'get_calendar', $apiParams);
        return CalendarResponse::map($parsedData);
    }

    public function calendarChanges(Carbon $since): CalendarChangesResponse
    {
        $apiParams = [
            'since' => $since->toIso8601String(),
        ];
        $parsedData = $this->performApiCall('BEXML', 'list_calendar_changes', $apiParams);
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
            'arrival_date' => $arrivalDate->toDateString(),
            'departure_date' => $departureDate->toDateString(),
            'adults' => $numAdults,
        ];
        if ($numChildren !== null) {
            $apiParams['children'] = $numChildren;
        }
        if ($numBabies !== null) {
            $apiParams['babies'] = $numBabies;
        }
        $parsedData = $this->performApiCall('BEXML', 'get_rate_for_stay', $apiParams);
        if (!$parsedData || !isset($parsedData['rate'])) {
            throw new MappingException('Invalid response structure for rate for stay: missing "rate" key.');
        }
        return RateResponse::map($parsedData['rate']);
    }

    public function createBooking(CreateBookingPayload $payload): CreateBookingResponse
    {
        $apiParams = $payload->toArray();
        $parsedData = $this->performApiCall('BEXML', 'create_booking', $apiParams);
        return CreateBookingResponse::map($parsedData);
    }

    public function finalizeBooking(int $externalBookingId): FinalizeBookingResponse
    {
        $apiParams = [
            'booking_id' => $externalBookingId,
            'overwrite_rates' => 1,
        ];
        $parsedData = $this->performApiCall('BEXML', 'finalize_booking', $apiParams);
        return FinalizeBookingResponse::map($parsedData);
    }

    public function cancelBooking(int $bookingId, string $reason): CancelBookingResponse
    {
        $apiParams = [
            'booking_id' => $bookingId,
            'reason' => $reason,
        ];
        $parsedData = $this->performApiCall('BEXML', 'cancel_booking', $apiParams);
        return CancelBookingResponse::map($parsedData);
    }

    public function viewBooking(int $bookingId): ViewBookingResponse
    {
        $apiParams = [
            'bookingid' => $bookingId,
        ];
        $parsedData = $this->performApiCall('BEXML', 'booking_view', $apiParams);
        return ViewBookingResponse::map($parsedData);
    }

    private function getEndpoint(string $type): string
    {
        return "{$this->baseUrl}/{$type}";
    }
}
