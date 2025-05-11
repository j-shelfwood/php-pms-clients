<?php

namespace Shelfwood\PhpPms\BookingManager;

use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;
use Shelfwood\PhpPms\Http\XMLClient;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Payloads\CreateBookingPayload;
use Shelfwood\PhpPms\BookingManager\Responses\CancelBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CreateBookingResponse;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\FinalizeBookingResponse;

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
     * Get all properties from BookingManager
     *
     * @return PropertiesResponse
     */
    public function properties(): PropertiesResponse
    {
        $params = ['request' => 'list_properties'];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        $parsedArray = $responseArray[0] ?? [];
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
        $params = [
            'request' => 'list_property',
            'id' => $id,
        ];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        $parsedArray = $responseArray[0] ?? [];
        if (!$parsedArray || !isset($parsedArray['property'])) {
            throw new \RuntimeException('Invalid response structure for property');
        }
        return PropertyResponse::map($parsedArray['property']);
    }

    public function calendar(int $propertyId, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        $params = [
            'request' => 'get_calendar',
            'property_id' => $propertyId,
            'date_from' => $startDate->format('Ymd'),
            'date_to' => $endDate->format('Ymd'),
        ];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        return CalendarResponse::map($responseArray[0]);
    }

    public function calendarChanges(Carbon $since): CalendarChangesResponse
    {
        $params = [
            'request' => 'list_calendar_changes',
            'since' => $since->toIso8601String(),
        ];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        $parsedArray = $responseArray[0] ?? [];

        if (!$parsedArray || !isset($parsedArray['property'])) {
            $parsedArray['property'] = [];
        }
        if (isset($parsedArray['property']) && !is_array($parsedArray['property'])) {
            $parsedArray['property'] = [$parsedArray['property']];
        } elseif (isset($parsedArray['property']) && isset($parsedArray['property']['@attributes'])){
            $parsedArray['property'] = [$parsedArray['property']];
        }

        return CalendarChangesResponse::map($parsedArray);
    }

    public function rateForStay(int $propertyId, Carbon $arrivalDate, Carbon $departureDate, int $numAdults, ?int $numChildren = null, ?int $numBabies = null): RateResponse
    {
        $params = [
            'request' => 'get_rate_for_stay',
            'id' => $propertyId,
            'arrival_date' => $arrivalDate->toDateString(),
            'departure_date' => $departureDate->toDateString(),
            'adults' => $numAdults,
        ];

        if ($numChildren !== null) {
            $params['children'] = $numChildren;
        }

        if ($numBabies !== null) {
            $params['babies'] = $numBabies;
        }

        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        $parsedArray = $responseArray[0] ?? [];

        if (!$parsedArray || !isset($parsedArray['rate'])) {
            throw new \RuntimeException('Invalid response structure for rate for stay');
        }

        return RateResponse::map($parsedArray['rate']);
    }

    public function createBooking(CreateBookingPayload $payload): CreateBookingResponse
    {
        $params = array_merge(['request' => 'create_booking'], $payload->toArray());
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        return CreateBookingResponse::map($responseArray[0]);
    }

    public function finalizeBooking(int $externalBookingId): FinalizeBookingResponse
    {
        $params = [
            'request' => 'finalize_booking',
            'booking_id' => $externalBookingId,
            'overwrite_rates' => 1,
        ];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        return FinalizeBookingResponse::map($responseArray[0]);
    }

    public function cancelBooking(int $bookingId, string $reason): CancelBookingResponse
    {
        $params = [
            'request' => 'cancel_booking',
            'booking_id' => $bookingId,
            'reason' => $reason,
        ];
        $responseArray = $this->sendRequest('POST', $this->getEndpoint('BEXML'), ['form_params' => $params]);
        return CancelBookingResponse::map($responseArray[0]);
    }

    private function getEndpoint(string $type): string
    {
        return "{$this->baseUrl}/{$type}";
    }
}
