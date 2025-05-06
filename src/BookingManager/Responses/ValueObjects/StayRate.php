<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Collection;

class StayRate
{
    public function __construct(
        public readonly string $currency,
        public readonly float $total,
        public readonly float $final,
        public readonly StayTax $tax,
        public readonly float $fee,
        public readonly float $prepayment,
        public readonly float $balanceDue
    ) {}

    public static function fromXml(Collection|array $rateData): self
    {
        return new self(
            currency: (string) ($rateData->get('@attributes')['currency'] ?? ''),
            total: (float) ($rateData->get('total') ?? 0.0),
            final: (float) ($rateData->get('final') ?? 0.0),
            tax: StayTax::fromXml(collect($rateData->get('tax', []))),
            fee: (float) ($rateData->get('fee') ?? 0.0),
            prepayment: (float) ($rateData->get('prepayment') ?? 0.0),
            balanceDue: (float) ($rateData->get('balance_due') ?? 0.0)
        );
    }
}
