<?php

namespace Domain\Connections\Cubilis;

use App\Models\Booking;
use App\Models\Property;
use Domain\Dtos\BookingRate;
use Illuminate\Support\Carbon;
use Domain\Connections\XMLClient;
use Domain\Dtos\CreateBookingData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Domain\Dtos\CalendarListResponse;
use Domain\Dtos\PropertyListResponse;
use Domain\Dtos\CreateBookingResponse;
use Domain\Dtos\CalendarChangesResponse;
use Illuminate\Http\Client\RequestException;
use Domain\Connections\Cubilis\Dtos\BookingResponseDto;
use Domain\Connections\Cubilis\Dtos\RoomListResponseDto;

class CubilisAPI extends XMLClient
{
    protected string $endpoint;

    protected ?string $username;

    protected ?string $password;

    protected string $requestorType;

    protected ?string $requestorId;

    public function __construct(
        string $endpoint,
        ?string $username,
        ?string $password,
        string $requestorType,
        ?string $requestorId
    ) {
        $this->endpoint = $endpoint;
        $this->username = $username;
        $this->password = $password;
        $this->requestorType = $requestorType;
        $this->requestorId = $requestorId;
    }

    protected function sendRequest(string $xml): string
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withBody($xml, 'application/xml')
                ->post($this->endpoint);
            $response->throw();

            return $response->body();
        } catch (RequestException $e) {
            Log::error('Cubilis API request failed', ['endpoint' => $this->endpoint, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Build POS XML fragment based on requestor type and credentials.
     */
    protected function buildPOSXml(): string
    {
        if ($this->requestorType === '1') {
            $id = htmlspecialchars((string) $this->username, ENT_XML1);
            $password = htmlspecialchars((string) $this->password, ENT_XML1);

            return sprintf(
                '<POS><Source><RequestorID Type="%s" ID="%s" MessagePassword="%s"/></Source></POS>',
                $this->requestorType,
                $id,
                $password
            );
        }
        $id = htmlspecialchars((string) $this->requestorId, ENT_XML1);

        return sprintf(
            '<POS><Source><RequestorID Type="%s" ID="%s"/></Source></POS>',
            $this->requestorType,
            $id
        );
    }

    /**
     * Public alias for building POS fragment (used by tests).
     */
    public function buildPOS(): string
    {
        return $this->buildPOSXml();
    }

    protected function buildHotelRoomListRequest(): string
    {
        $pos = $this->buildPOSXml();

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<OTA_HotelRoomListRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">'
            .$pos
            .'<HotelRoomLists><HotelRoomList/></HotelRoomLists>'
            .'</OTA_HotelRoomListRQ>';
    }

    protected function buildHotelResRequest(CreateBookingData $data, string $mode): string
    {
        $pos = $this->buildPOSXml();
        $guestCount = sprintf('<GuestCount Count="%d"/>', $data->amount_adults + $data->amount_childs);
        $timeSpan = sprintf('<TimeSpan Start="%s" End="%s"/>', $data->start, $data->end);
        $guest = sprintf(
            '<Guest><PersonName><GivenName>%s</GivenName><Surname>%s</Surname></PersonName></Guest>',
            htmlspecialchars($data->name_first, ENT_XML1),
            htmlspecialchars($data->name_last, ENT_XML1)
        );
        $globalInfo = '<ResGlobalInfo>'.$guestCount.$timeSpan.'</ResGlobalInfo>';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<OTA_HotelResRQ Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">'
            .$pos
            .$globalInfo
            .$guest
            .'</OTA_HotelResRQ>';
    }

    public function getRateForStay(int $externalId, Carbon $arrival, Carbon $departure): BookingRate
    {
        return BookingRate::mock($externalId, $arrival, $departure);
    }

    public function getAllProperties(): PropertyListResponse
    {
        $xml = $this->buildHotelRoomListRequest();
        $body = $this->sendRequest($xml);
        $parsed = $this->parseXml($body);

        // Use DTO to map raw XML to domain PropertyListResponse
        $dto = RoomListResponseDto::fromCollection($parsed);

        return $dto->toDomain();
    }

    public function getCalendarChanges(Carbon $since): CalendarChangesResponse
    {
        throw new \BadMethodCallException('Calendar changes pull not supported for Cubilis');
    }

    public function getCalendarForDateRange(Property $property, Carbon $startDate, Carbon $endDate): CalendarListResponse
    {
        throw new \BadMethodCallException('Calendar range pull not supported for Cubilis');
    }

    public function createBooking(CreateBookingData $data): CreateBookingResponse
    {
        $xml = $this->buildHotelResRequest($data, 'Push');
        $body = $this->sendRequest($xml);
        $parsed = $this->parseXml($body);

        // Map response using DTO
        $dto = BookingResponseDto::fromCollection($parsed);

        return $dto->toDomain();
    }

    public function finalizeBooking(Booking $booking): void
    {
        Log::info('Cubilis finalizeBooking stubbed', ['booking_id' => $booking->id]);
    }
}
