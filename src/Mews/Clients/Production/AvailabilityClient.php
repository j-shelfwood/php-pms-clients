<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Services\DateConverter;

class AvailabilityClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    /**
     * Get availability for a service across a date range
     *
     * Retrieves availability and occupancy metrics for all resource categories within a service.
     *
     * @see https://api.mews.com/Swagger/connector/swagger.yaml (services/getAvailability/2024-01-22)
     * @param GetAvailabilityPayload $payload Request payload
     * @return AvailabilityResponse Availability data with ResourceCategoryAvailabilities, TimeUnitStartsUtc
     * @throws MewsApiException
     */
    public function get(GetAvailabilityPayload $payload): AvailabilityResponse
    {
        $enterpriseTimezone = $this->httpClient->getEnterpriseTimezoneIdentifier();

        $normalizedPayload = new GetAvailabilityPayload(
            serviceId: $payload->serviceId,
            firstTimeUnitStartUtc: $this->toEnterpriseMidnightBoundaryUtc($payload->firstTimeUnitStartUtc, $enterpriseTimezone),
            lastTimeUnitStartUtc: $this->toEnterpriseMidnightBoundaryUtc($payload->lastTimeUnitStartUtc, $enterpriseTimezone),
            metrics: $payload->metrics,
            resourceCategoryIds: $payload->resourceCategoryIds,
        );

        $body = $this->httpClient->buildRequestBody($normalizedPayload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/services/getAvailability/2024-01-22', $body);

        return AvailabilityResponse::map($response);
    }

    private function toEnterpriseMidnightBoundaryUtc(Carbon $date, string $enterpriseTimezone): Carbon
    {
        // Interpret input as a calendar date (not an instant) and normalize to UTC midnight first.
        $utcDate = Carbon::parse($date->format('Y-m-d'), 'UTC');
        return DateConverter::toEnterpriseMidnightUtc($utcDate, $enterpriseTimezone);
    }
}
