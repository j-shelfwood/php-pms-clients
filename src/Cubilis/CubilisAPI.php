<?php

namespace PhpPms\Clients\Cubilis;

use PhpPms\Clients\Models\Booking; // TODO: Refactor out App specific models - Updated namespace
use PhpPms\Clients\Models\Property; // TODO: Refactor out App specific models - Updated namespace
use PhpPms\Clients\Dtos\BookingRate; // Updated namespace
use Illuminate\Support\Carbon; // TODO: Consider replacing with DateTimeImmutable
use PhpPms\Clients\XMLClient;
use PhpPms\Clients\Dtos\CreateBookingData; // Updated namespace
use PhpPms\Clients\Dtos\CalendarListResponse; // Updated namespace
use PhpPms\Clients\Dtos\PropertyListResponse; // Updated namespace
use PhpPms\Clients\Dtos\CreateBookingResponse; // Updated namespace
use PhpPms\Clients\Dtos\CalendarChangesResponse; // Updated namespace
use PhpPms\Clients\Exceptions\HttpClientException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Psr\Log\LoggerInterface;
// Removed: use Illuminate\Support\Facades\Log;
// Removed: use Illuminate\Support\Facades\Http;
// Removed: use Illuminate\Http\Client\RequestException;
use PhpPms\Clients\Cubilis\Dtos\BookingResponseDto; // Updated namespace
use PhpPms\Clients\Cubilis\Dtos\RoomListResponseDto; // Updated namespace

class CubilisAPI extends XMLClient
{
    // Removed: protected string $endpoint; // Now uses $this->baseUrl from parent
    protected ?string $username;
    protected ?string $password;
    protected string $requestorType;
    protected ?string $requestorId;
    // httpClient and logger are inherited from XMLClient

    public function __construct(
        string $endpoint, // This will be passed as baseUrl to parent
        ?string $username,
        ?string $password,
        string $requestorType,
        ?string $requestorId,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($endpoint, '', $httpClient, $logger); // Pass endpoint as baseUrl, empty string for apiKey
        $this->username = $username;
        $this->password = $password;
        $this->requestorType = $requestorType;
        $this->requestorId = $requestorId;
    }

    protected function sendRequest(string $xml): string
    {
        try {
            $options = [
                'body' => $xml,
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                // 'http_errors' => true, // Guzzle default: throws exceptions for 4xx/5xx responses
            ];

            if ($this->username !== null && $this->password !== null) {
                $options['auth'] = [$this->username, $this->password];
            }

            $response = $this->httpClient->request('POST', $this->baseUrl, $options);

            return $response->getBody()->getContents();
        } catch (GuzzleRequestException $e) {
            $this->logger->error('Cubilis API request failed', [
                'endpoint' => $this->baseUrl,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
            throw new HttpClientException('Cubilis API request failed: ' . $e->getMessage(), $e->getCode(), $e);
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
        $this->logger->info('Cubilis finalizeBooking stubbed', ['booking_id' => $booking->id]);
    }
}
