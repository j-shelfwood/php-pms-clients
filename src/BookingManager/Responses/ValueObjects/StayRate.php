<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

class StayRate
{
    public function __construct(
        public readonly float $final,
        public readonly float $prepayment,
        public readonly float $balanceDue,
        public readonly StayTax $tax
    ) {}

    public static function fromXml(array $rateData): self
    {
        return new self(
            final: (float) ($rateData['final'] ?? 0.0),
            prepayment: (float) ($rateData['prepayment'] ?? 0.0),
            balanceDue: (float) ($rateData['balance_due'] ?? 0.0),
            tax: StayTax::fromXml($rateData['tax'] ?? [])
        );
    }
}
