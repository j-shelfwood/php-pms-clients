<?php

namespace Shelfwood\PhpPms\Mews\Responses;

class CalendarResponse
{
    public function __construct(
        public readonly AvailabilityResponse $availability,
        public readonly ?PricingResponse $pricing,
    ) {}

    public static function map(array $availabilityData, array $pricingData): self
    {
        return new self(
            availability: AvailabilityResponse::map($availabilityData),
            pricing: PricingResponse::map($pricingData)
        );
    }
}
