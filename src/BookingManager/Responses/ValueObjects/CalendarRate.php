<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class CalendarRate
{
    public function __construct(
        public readonly float $percentage,
        public readonly string $currency,
        public readonly float $total,
        public readonly float $final,
        public readonly CalendarTax $tax,
        public readonly float $fee,
        public readonly float $prepayment,
        public readonly float $balanceDue
    ) {}

    public static function fromXml(Collection|array $rateData): self
    {
        $sourceData = $rateData instanceof Collection ? $rateData : new Collection($rateData);
        $attributes = new Collection($sourceData->get('@attributes', []));

        return new self(
            percentage: (float) ($attributes->get('percentage', 0.0)),
            currency: (string) ($attributes->get('currency', '')),
            total: (float) ($sourceData->get('total') ?? 0.0),
            final: (float) ($sourceData->get('final') ?? 0.0),
            tax: CalendarTax::fromXml(new Collection($sourceData->get('tax', []))),
            fee: (float) ($sourceData->get('fee') ?? 0.0),
            prepayment: (float) ($sourceData->get('prepayment') ?? 0.0),
            balanceDue: (float) ($sourceData->get('balance_due') ?? 0.0)
        );
    }
}
