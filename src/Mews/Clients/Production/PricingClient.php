<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Payloads\GetPricingPayload;
use Shelfwood\PhpPms\Mews\Responses\RatesResponse;
use Shelfwood\PhpPms\Mews\Responses\PricingResponse;
use Shelfwood\PhpPms\Mews\Responses\CalendarResponse;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

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
        $body = $this->httpClient->buildRequestBody($payload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/rates/getPricing', $body);

        return PricingResponse::map($response);
    }

    /**
     * Get calendar/availability data for a service across a date range
     *
     * Combines availability and pricing data for calendar display by fetching
     * service availability metrics and rate pricing information, then merging
     * them into a unified calendar response.
     *
     * @see https://mews-systems.gitbook.io/connector-api/operations/services#get-service-availability
     * @see https://mews-systems.gitbook.io/connector-api/operations/rates#get-all-rates
     * @see https://mews-systems.gitbook.io/connector-api/operations/rates#get-rate-pricing
     * @param string $serviceId Service UUID
     * @param Carbon $start Start date (UTC)
     * @param Carbon $end End date (UTC)
     * @param int $adults Number of adults (default 2)
     * @param int $children Number of children (default 0)
     * @return CalendarResponse Calendar data with Availability, Pricing, ServiceId, date range
     * @throws MewsApiException
     */
    public function getCalendar(
        string $serviceId,
        Carbon $start,
        Carbon $end,
        int $adults = 2,
        int $children = 0
    ): CalendarResponse {
        // Get availability for the date range
        $availabilityPayload = new GetAvailabilityPayload(
            serviceId: $serviceId,
            start: $start,
            end: $end
        );
        $availability = $this->availabilityClient->get($availabilityPayload);

        // Get rates for pricing information
        $rates = $this->getServiceRates($serviceId);

        // Find best public rate
        $publicRates = array_filter($rates->rates, function ($rate) {
            return $rate->isActive &&
                   $rate->type === 'Public' &&
                   $rate->baseRateId === null;
        });

        $pricing = null;
        if (!empty($publicRates)) {
            $bestRate = array_values($publicRates)[0];
            $pricingPayload = new GetPricingPayload(
                rateId: $bestRate->id,
                start: $start,
                end: $end,
                adults: $adults,
                children: $children
            );
            $pricing = $this->getPricing($pricingPayload);
        }

        return new CalendarResponse(
            availability: $availability,
            pricing: $pricing,
            serviceId: $serviceId,
            startUtc: $start,
            endUtc: $end
        );
    }
}
