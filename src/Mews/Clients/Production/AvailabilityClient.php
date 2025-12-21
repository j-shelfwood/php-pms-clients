<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

class AvailabilityClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    /**
     * Get availability for a service across a date range
     *
     * Retrieves availability metrics for all resource categories within a service.
     * Returns daily availability counts across the specified date range.
     *
     * @see https://mews-systems.gitbook.io/connector-api/operations/services#get-service-availability
     * @param GetAvailabilityPayload $payload Request payload
     * @return AvailabilityResponse Availability data with CategoryAvailabilities, TimeUnitStartsUtc arrays
     * @throws MewsApiException
     */
    public function get(GetAvailabilityPayload $payload): AvailabilityResponse
    {
        $body = $this->httpClient->buildRequestBody($payload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/services/getAvailability', $body);

        return AvailabilityResponse::map($response);
    }
}
