<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection;

class StayRate
{
    public function __construct(
        public readonly float $final,
        public readonly float $prepayment,
        public readonly float $balanceDue,
        public readonly StayTax $tax
    ) {}

    public static function fromXml(Collection|array $rateData): self
    {
        return new self(
            final: (float) ($rateData->get('final') ?? 0.0),
            prepayment: (float) ($rateData->get('prepayment') ?? 0.0),
            balanceDue: (float) ($rateData->get('balance_due') ?? 0.0),
            tax: StayTax::fromXml(collect($rateData->get('tax', [])))
        );
    }
}
