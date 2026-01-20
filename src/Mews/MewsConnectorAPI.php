<?php

namespace Shelfwood\PhpPms\Mews;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

// Production clients (used in every booking flow)
use Shelfwood\PhpPms\Mews\Clients\Production\AvailabilityClient;
use Shelfwood\PhpPms\Mews\Clients\Production\PricingClient;
use Shelfwood\PhpPms\Mews\Clients\Production\CustomersClient;
use Shelfwood\PhpPms\Mews\Clients\Production\ReservationsClient;
use Shelfwood\PhpPms\Mews\Clients\Production\RestrictionsClient;

// Validation clients (used only in background sync)
use Shelfwood\PhpPms\Mews\Clients\Validation\ServicesClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourceCategoriesClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourcesClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourceCategoryAssignmentsClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\AgeCategoriesClient;

// Response types
use Shelfwood\PhpPms\Mews\Responses\ServicesResponse;
use Shelfwood\PhpPms\Mews\Responses\ResourceCategoriesResponse;
use Shelfwood\PhpPms\Mews\Responses\ResourcesResponse;
use Shelfwood\PhpPms\Mews\Responses\ResourceCategoryAssignmentsResponse;
use Shelfwood\PhpPms\Mews\Responses\AgeCategoriesResponse;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\RatesResponse;
use Shelfwood\PhpPms\Mews\Responses\PricingResponse;
use Shelfwood\PhpPms\Mews\Responses\CalendarResponse;
use Shelfwood\PhpPms\Mews\Responses\CustomersResponse;
use Shelfwood\PhpPms\Mews\Responses\ReservationsResponse;
use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceBlock;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

// Payload types
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Payloads\GetPricingPayload;
use Shelfwood\PhpPms\Mews\Payloads\SearchCustomersPayload;
use Shelfwood\PhpPms\Mews\Payloads\CreateCustomerPayload;
use Shelfwood\PhpPms\Mews\Payloads\CreateReservationPayload;
use Shelfwood\PhpPms\Mews\Payloads\UpdateReservationPayload;

/**
 * Mews Connector API Facade
 *
 * Provides a comprehensive wrapper for all Mews Connector API endpoints.
 * Handles authentication, request formatting, error handling, and response parsing.
 *
 * @see https://mews-systems.gitbook.io/connector-api
 */
class MewsConnectorAPI
{
    private MewsHttpClient $httpClient;
    private ?string $webhookSecret;
    private ServicesClient $servicesClient;
    private ResourceCategoriesClient $resourceCategoriesClient;
    private ResourcesClient $resourcesClient;
    private ResourceCategoryAssignmentsClient $resourceCategoryAssignmentsClient;
    private AgeCategoriesClient $ageCategoriesClient;
    private AvailabilityClient $availabilityClient;
    private PricingClient $pricingClient;
    private CustomersClient $customersClient;
    private ReservationsClient $reservationsClient;
    private RestrictionsClient $restrictionsClient;

    /**
     * @param MewsConfig $config Mews API configuration
     * @param Client|null $guzzleClient Optional Guzzle HTTP client for making requests
     * @param LoggerInterface|null $logger Optional logger for debugging
     */
    public function __construct(
        MewsConfig $config,
        ?Client $guzzleClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->httpClient = new MewsHttpClient(
            $config,
            $guzzleClient ?? new Client(),
            $logger
        );

        $this->webhookSecret = $config->webhookSecret;

        // Production clients - used in every booking flow
        $this->availabilityClient = new AvailabilityClient($this->httpClient);
        $this->pricingClient = new PricingClient($this->httpClient, $this->availabilityClient);
        $this->customersClient = new CustomersClient($this->httpClient);
        $this->reservationsClient = new ReservationsClient($this->httpClient);
        $this->restrictionsClient = new RestrictionsClient($this->httpClient);

        // Validation clients - used only in background property sync
        $this->servicesClient = new ServicesClient($this->httpClient);
        $this->resourceCategoriesClient = new ResourceCategoriesClient($this->httpClient);
        $this->resourcesClient = new ResourcesClient($this->httpClient);
        $this->resourceCategoryAssignmentsClient = new ResourceCategoryAssignmentsClient($this->httpClient);
        $this->ageCategoriesClient = new AgeCategoriesClient($this->httpClient);
    }

    // ========================================================================
    // SERVICES (PROPERTIES)
    // ========================================================================

    /**
     * Get all services (properties) from the enterprise
     *
     * @param array|null $serviceIds Filter by specific service IDs
     * @return ServicesResponse Array of service objects
     * @throws MewsApiException
     */
    public function getAllServices(?array $serviceIds = null): ServicesResponse
    {
        return $this->servicesClient->getAll($serviceIds);
    }

    /**
     * Get a single service by ID
     *
     * @param string $serviceId Service UUID
     * @return Service Service object
     * @throws MewsApiException
     */
    public function getService(string $serviceId): Service
    {
        return $this->servicesClient->getById($serviceId);
    }

    /**
     * Get resource categories for a service
     *
     * Resource categories represent different types of rooms/units
     * (e.g., "2 Bedroom Suite", "Studio Apartment")
     *
     * @param string $serviceId Service UUID
     * @return ResourceCategoriesResponse Array of resource category objects
     * @throws MewsApiException
     */
    public function getResourceCategories(string $serviceId): ResourceCategoriesResponse
    {
        return $this->resourceCategoriesClient->getForService($serviceId);
    }

    /**
     * Get all resources (individual units/apartments) for a service
     *
     * Resources represent individual bookable units (e.g., "Unit 101", "CS-339")
     * within a ResourceCategory (e.g., "2 Bedroom Suite")
     *
     * @param string $serviceId Service UUID
     * @return ResourcesResponse Array of resource objects
     * @throws MewsApiException
     */
    public function getAllResources(string $serviceId): ResourcesResponse
    {
        return $this->resourcesClient->getForService($serviceId);
    }

    /**
     * Get resources for a specific resource category
     *
     * @param string $categoryId Resource category UUID
     * @return ResourcesResponse Array of resource objects
     * @throws MewsApiException
     */
    public function getResourcesForCategory(string $categoryId): ResourcesResponse
    {
        return $this->resourcesClient->getForCategory($categoryId);
    }

    /**
     * Get a single resource by ID (direct lookup)
     *
     * @param string $resourceId Resource UUID
     * @return Resource Resource object
     * @throws MewsApiException
     */
    public function getResourceById(string $resourceId): Resource
    {
        return $this->resourcesClient->getById($resourceId);
    }

    /**
     * Get resource category assignments
     *
     * Returns the mapping between resources and resource categories.
     * Resources don't have a direct ResourceCategoryId field - this endpoint
     * provides the separate assignments table linking them together.
     *
     * @param array|null $resourceCategoryIds Category IDs to get assignments for
     * @param array|null $resourceIds Resource IDs to get assignments for
     * @param int|null $limitCount Optional: Pagination limit (1-1000)
     * @return ResourceCategoryAssignmentsResponse Array of assignment objects with ResourceId and CategoryId
     * @throws MewsApiException
     */
    public function getResourceCategoryAssignments(
        ?array $resourceCategoryIds = null,
        ?array $resourceIds = null,
        ?int $limitCount = 1000
    ): ResourceCategoryAssignmentsResponse {
        return $this->resourceCategoryAssignmentsClient->getAll($resourceCategoryIds, $resourceIds, limitCount: $limitCount);
    }

    /**
     * Get resource category assignment for a specific resource
     *
     * @param string $resourceId Resource UUID
     * @return ResourceCategoryAssignment|null Assignment object or null if not found
     * @throws MewsApiException
     */
    public function getResourceCategoryAssignment(string $resourceId): ?ResourceCategoryAssignment
    {
        return $this->resourceCategoryAssignmentsClient->getForResource($resourceId);
    }

    // ========================================================================
    // AVAILABILITY & PRICING
    // ========================================================================

    /**
     * Get availability for a service across a date range
     *
     * Supports two calling styles:
     * 1. With Payload object: getAvailability($payload)
     * 2. With named parameters: getAvailability($serviceId, $firstTimeUnitStartUtc, $lastTimeUnitStartUtc)
     *
     * @param GetAvailabilityPayload|string $payloadOrServiceId Payload object OR service UUID
     * @param Carbon|null $firstTimeUnitStartUtc Start boundary (UTC)
     * @param Carbon|null $lastTimeUnitStartUtc End boundary (UTC)
     * @return AvailabilityResponse Availability data with metrics per time interval
     * @throws MewsApiException
     */
    public function getAvailability(
        GetAvailabilityPayload|string $payloadOrServiceId,
        ?Carbon $firstTimeUnitStartUtc = null,
        ?Carbon $lastTimeUnitStartUtc = null
    ): AvailabilityResponse {
        // Guard: If already a payload, use it directly
        if ($payloadOrServiceId instanceof GetAvailabilityPayload) {
            return $this->availabilityClient->get($payloadOrServiceId);
        }

        // Build payload from named parameters
        $payload = new GetAvailabilityPayload(
            serviceId: $payloadOrServiceId,
            firstTimeUnitStartUtc: $firstTimeUnitStartUtc ?? throw new \InvalidArgumentException('FirstTimeUnitStartUtc is required'),
            lastTimeUnitStartUtc: $lastTimeUnitStartUtc ?? throw new \InvalidArgumentException('LastTimeUnitStartUtc is required')
        );

        return $this->availabilityClient->get($payload);
    }

    /**
     * Get all rates for a service
     *
     * @param string $serviceId Service UUID
     * @return RatesResponse Array of rate objects
     * @throws MewsApiException
     */
    public function getServiceRates(string $serviceId): RatesResponse
    {
        return $this->pricingClient->getServiceRates($serviceId);
    }

    /**
     * Get pricing for a specific rate across a date range
     *
     * Supports two calling styles:
     * 1. With Payload object: getPricing($payload)
     * 2. With named parameters: getPricing($rateId, $firstTimeUnitStartUtc, $lastTimeUnitStartUtc)
     *
     * @param GetPricingPayload|string $payloadOrRateId Payload object OR rate UUID
     * @param Carbon|null $firstTimeUnitStartUtc Start boundary (UTC)
     * @param Carbon|null $lastTimeUnitStartUtc End boundary (UTC)
     * @return PricingResponse Pricing data with per-night rates
     * @throws MewsApiException
     */
    public function getPricing(
        GetPricingPayload|string $payloadOrRateId,
        ?Carbon $firstTimeUnitStartUtc = null,
        ?Carbon $lastTimeUnitStartUtc = null
    ): PricingResponse {
        // Guard: If already a payload, use it directly
        if ($payloadOrRateId instanceof GetPricingPayload) {
            return $this->pricingClient->getPricing($payloadOrRateId);
        }

        // Build payload from named parameters
        $payload = new GetPricingPayload(
            rateId: $payloadOrRateId,
            firstTimeUnitStartUtc: $firstTimeUnitStartUtc ?? throw new \InvalidArgumentException('FirstTimeUnitStartUtc is required'),
            lastTimeUnitStartUtc: $lastTimeUnitStartUtc ?? throw new \InvalidArgumentException('LastTimeUnitStartUtc is required')
        );

        return $this->pricingClient->getPricing($payload);
    }

    /**
     * Get calendar/availability data for a service across a date range
     *
     * This combines availability and pricing data for calendar display
     *
     * @param string $serviceId Service UUID
     * @param Carbon $start Start date (UTC)
     * @param Carbon $end End date (UTC)
     * @param int $adults Number of adults (default 2)
     * @param int $children Number of children (default 0)
     * @return CalendarResponse Calendar data with availability and pricing per day
     * @throws MewsApiException
     */
    public function getCalendar(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        int $adults = 2,
        int $children = 0
    ): CalendarResponse {
        return $this->pricingClient->getCalendar($serviceId, $start, $end, $adults, $children);
    }

    // ========================================================================
    // RESTRICTIONS
    // ========================================================================

    /**
     * Get all restrictions for a service across a date range
     *
     * @param string $serviceId Service UUID
     * @param Carbon $start Start date (UTC)
     * @param Carbon $end End date (UTC)
     * @param array|null $resourceCategoryIds Specific categories to check (optional)
     * @return RestrictionsResponse All restrictions data
     * @throws MewsApiException
     */
    public function getRestrictions(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        ?array $resourceCategoryIds = null
    ): RestrictionsResponse {
        return $this->restrictionsClient->getAll($serviceId, $start, $end, $resourceCategoryIds);
    }

    // ========================================================================
    // CUSTOMERS
    // ========================================================================

    /**
     * Find or create a customer in Mews
     *
     * Supports two calling styles:
     * 1. With Payload object: findOrCreateCustomer($payload)
     * 2. With named parameters: findOrCreateCustomer($email, $firstName, $lastName, ...)
     *
     * @param CreateCustomerPayload|string $payloadOrEmail Payload object OR customer email
     * @param string|null $firstName Customer first name (if using named params)
     * @param string|null $lastName Customer last name (if using named params)
     * @param string|null $phone Customer phone number
     * @param string|null $nationalityCode ISO country code
     * @param string|null $birthDate Birth date (ISO format)
     * @return string Customer UUID
     * @throws MewsApiException
     */
    public function findOrCreateCustomer(
        CreateCustomerPayload|string $payloadOrEmail,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phone = null,
        ?string $nationalityCode = null,
        ?string $birthDate = null
    ): string {
        // Guard: If already a payload, use it directly
        if ($payloadOrEmail instanceof CreateCustomerPayload) {
            return $this->customersClient->findOrCreate($payloadOrEmail);
        }

        // Build payload from named parameters
        $payload = new CreateCustomerPayload(
            email: $payloadOrEmail,
            firstName: $firstName,
            lastName: $lastName,
            phone: $phone,
            nationalityCode: $nationalityCode,
            birthDate: $birthDate
        );

        return $this->customersClient->findOrCreate($payload);
    }

    /**
     * Search for customers by email
     *
     * @param string $email Customer email address
     * @return CustomersResponse Array of matching customer objects
     * @throws MewsApiException
     */
    public function searchCustomers(string $email): CustomersResponse
    {
        $payload = new SearchCustomersPayload(emails: [$email]);
        return $this->customersClient->search($payload);
    }

    /**
     * Get customer by ID
     *
     * @param string $customerId Customer UUID
     * @return Customer Customer object
     * @throws MewsApiException
     */
    public function getCustomer(string $customerId): Customer
    {
        return $this->customersClient->getById($customerId);
    }

    // ========================================================================
    // RESERVATIONS
    // ========================================================================

    /**
     * Create a new reservation from payload
     *
     * @param CreateReservationPayload $payload Reservation creation payload
     * @param bool $sendConfirmationEmail Whether to send confirmation email
     * @return Reservation Created reservation object
     * @throws MewsApiException
     */
    public function createReservation(CreateReservationPayload $payload, bool $sendConfirmationEmail = true): Reservation
    {
        return $this->reservationsClient->create($payload, $sendConfirmationEmail);
    }

    /**
     * Create a new reservation from parameters
     *
     * @param string $serviceId Service UUID
     * @param string $customerId Customer UUID
     * @param string $rateId Rate UUID
     * @param Carbon $startUtc Start (UTC)
     * @param Carbon $endUtc End (UTC)
     * @param array $personCounts Person counts array
     * @param string|null $requestedCategoryId Requested category UUID (optional)
     * @param ReservationState $state Reservation state
     * @param string|null $notes Notes (optional)
     * @param Carbon|null $releasedUtc Release date/time (required for Optional)
     * @param bool $sendConfirmationEmail Whether to send confirmation email
     * @return Reservation Created reservation object
     * @throws MewsApiException
     */
    public function createReservationFromParams(
        string $serviceId,
        string $customerId,
        string $rateId,
        Carbon $startUtc,
        Carbon $endUtc,
        array $personCounts,
        ?string $requestedCategoryId = null,
        ReservationState $state = ReservationState::Confirmed,
        ?string $notes = null,
        ?Carbon $releasedUtc = null,
        bool $sendConfirmationEmail = true
    ): Reservation {
        $payload = new CreateReservationPayload(
            serviceId: $serviceId,
            customerId: $customerId,
            rateId: $rateId,
            startUtc: $startUtc,
            endUtc: $endUtc,
            personCounts: $personCounts,
            requestedCategoryId: $requestedCategoryId,
            state: $state,
            notes: $notes,
            releaseUtc: $releasedUtc
        );

        return $this->reservationsClient->create($payload, $sendConfirmationEmail);
    }

    /**
     * Get reservation by ID
     *
     * @param string $reservationId Reservation UUID
     * @return Reservation Reservation object
     * @throws MewsApiException
     */
    public function getReservation(string $reservationId): Reservation
    {
        return $this->reservationsClient->getById($reservationId);
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
    public function getReservations(
        string $serviceId,
        Carbon $startDate,
        Carbon $endDate,
        ?array $states = null
    ): ReservationsResponse {
        return $this->reservationsClient->getAll($serviceId, $startDate, $endDate, $states);
    }

    /**
     * Update an existing reservation
     *
     * @param UpdateReservationPayload $payload Update payload
     * @return Reservation Updated reservation object
     * @throws MewsApiException
     */
    public function updateReservation(UpdateReservationPayload $payload): Reservation
    {
        return $this->reservationsClient->update($payload);
    }

    /**
     * Cancel a reservation
     *
     * @param string $reservationId Reservation UUID
     * @param string $reason Cancellation reason
     * @return Reservation Cancelled reservation object
     * @throws MewsApiException
     */
    public function cancelReservation(string $reservationId, string $reason): Reservation
    {
        return $this->reservationsClient->cancel($reservationId, $reason);
    }

    /**
     * Update reservation state (e.g., Optional → Confirmed)
     *
     * @param string $reservationId Reservation UUID
     * @param string $newState New state ('Optional', 'Confirmed', 'Canceled', etc.)
     * @return Reservation Updated reservation object
     * @throws MewsApiException
     */
    public function updateReservationState(string $reservationId, ReservationState $newState): Reservation
    {
        return $this->reservationsClient->updateState($reservationId, $newState);
    }

    // ========================================================================
    // AGE CATEGORIES
    // ========================================================================

    /**
     * Get all age categories for a service
     *
     * @param string $serviceId Service UUID
     * @return AgeCategoriesResponse Array of age category objects
     * @throws MewsApiException
     */
    public function getAgeCategories(string $serviceId): AgeCategoriesResponse
    {
        return $this->ageCategoriesClient->getAll($serviceId);
    }

    /**
     * Get adult age category for a service
     *
     * @param string $serviceId Service UUID
     * @return AgeCategory|null Age category object or null if not found
     * @throws MewsApiException
     */
    public function getAdultAgeCategory(string $serviceId): ?AgeCategory
    {
        return $this->ageCategoriesClient->getAdultCategory($serviceId);
    }

    /**
     * Get child age category for a service
     *
     * @param string $serviceId Service UUID
     * @return AgeCategory|null Age category object or null if not found
     * @throws MewsApiException
     */
    public function getChildAgeCategory(string $serviceId): ?AgeCategory
    {
        return $this->ageCategoriesClient->getChildCategory($serviceId);
    }

    /**
     * Get ReservationsClient instance (for direct access to reservation methods)
     *
     * @return ReservationsClient
     */
    public function getReservationsClient(): ReservationsClient
    {
        return $this->reservationsClient;
    }

    // ========================================================================
    // RESOURCE BLOCKS
    // ========================================================================

    /**
     * Get resource block details by ID
     *
     * Fetches detailed information about a specific resource block (calendar availability block).
     * Resource blocks represent periods where a resource is blocked from booking, either manually
     * by property managers or automatically by reservations.
     *
     * @param string $serviceId Service UUID
     * @param string $blockId Resource block UUID
     * @return ResourceBlock|null Resource block details or null if not found
     * @throws MewsApiException
     * @see https://mews-systems.gitbook.io/connector-api/operations/resourceblocks
     */
    public function getResourceBlock(string $serviceId, string $blockId): ?ResourceBlock
    {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => [$serviceId],
            'ResourceBlockIds' => [$blockId],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/resourceBlocks/get', $body);

        if (empty($response['ResourceBlocks'])) {
            return null;
        }

        return ResourceBlock::fromApiResponse($response['ResourceBlocks'][0]);
    }

    // ========================================================================
    // CONFIGURATION & WEBHOOKS
    // ========================================================================

    /**
     * Get webhook secret for signature verification
     *
     * Used to verify incoming webhook requests from Mews are authentic.
     *
     * @return string|null Webhook secret or null if not configured
     */
    public function getWebhookSecret(): ?string
    {
        return $this->webhookSecret;
    }

    /**
     * Get enterprise configuration including timezone
     *
     * Returns enterprise details, timezone, and server time.
     *
     * @return array Enterprise configuration data
     * @throws MewsApiException
     */
    public function getConfiguration(): array
    {
        return $this->httpClient->getConfiguration();
    }

    /**
     * Get enterprise timezone identifier (convenience method)
     *
     * @return string IANA timezone identifier (e.g., "Europe/Budapest")
     * @throws MewsApiException
     */
    public function getEnterpriseTimezone(): string
    {
        return $this->httpClient->getEnterpriseTimezoneIdentifier();
    }
}
