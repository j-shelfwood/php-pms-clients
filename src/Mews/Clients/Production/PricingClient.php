<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Payloads\GetPricingPayload;
use Shelfwood\PhpPms\Mews\Responses\RatesResponse;
use Shelfwood\PhpPms\Mews\Responses\PricingResponse;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Services\DateConverter;

class PricingClient
{
    public function __construct(
        private MewsHttpClient $httpClient,
        private AvailabilityClient $availabilityClient
    ) {}

    /**
     * Get all rates for a service
     *
     * Retrieves all rate plans configured for a service, including public rates,
     * private rates, and availability block rates.
     *
     * @see https://mews-systems.gitbook.io/connector-api/operations/rates#get-all-rates
     * @param string $serviceId Service UUID
     * @return RatesResponse Array of rate objects with Id, Name, Type, IsActive, BaseRateId, etc.
     * @throws MewsApiException
     */
    public function getServiceRates(string $serviceId): RatesResponse
    {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => [$serviceId],
            'Limitation' => [
                'Count' => 1000,
            ],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/rates/getAll', $body);

        return RatesResponse::map($response);
    }

    /**
     * Get pricing for a specific rate across a date range
     *
     * Retrieves daily pricing for a rate, including base prices and category-specific
     * price adjustments. Returns BaseAmountPrices (rate default) and CategoryPrices
     * (resource category overrides) for accurate per-room-type pricing.
     *
     * @see https://mews-systems.gitbook.io/connector-api/operations/rates#get-rate-pricing
     * @param GetPricingPayload $payload Request payload
     * @return PricingResponse Pricing data with TimeUnitStartsUtc, BaseAmountPrices, CategoryPrices, Currency
     * @throws MewsApiException
     */
    public function getPricing(GetPricingPayload $payload): PricingResponse
    {
        $enterpriseTimezone = $this->httpClient->getEnterpriseTimezoneIdentifier();

        $normalizedPayload = new GetPricingPayload(
            rateId: $payload->rateId,
            firstTimeUnitStartUtc: $this->toEnterpriseMidnightBoundaryUtc($payload->firstTimeUnitStartUtc, $enterpriseTimezone),
            lastTimeUnitStartUtc: $this->toEnterpriseMidnightBoundaryUtc($payload->lastTimeUnitStartUtc, $enterpriseTimezone),
        );

        $body = $this->httpClient->buildRequestBody($normalizedPayload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/rates/getPricing', $body);

        return PricingResponse::map($response);
    }

    private function toEnterpriseMidnightBoundaryUtc(Carbon $date, string $enterpriseTimezone): Carbon
    {
        $utcDate = Carbon::parse($date->format('Y-m-d'), 'UTC');
        return DateConverter::toEnterpriseMidnightUtc($utcDate, $enterpriseTimezone);
    }
}
