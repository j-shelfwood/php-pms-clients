<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



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

    public static function fromXml(array $rateData): self
    {
        $attributes = isset($rateData['@attributes']) ? $rateData['@attributes'] : [];
        return new self(
            percentage: (float)($attributes['percentage'] ?? 0.0),
            currency: (string)($attributes['currency'] ?? ''),
            total: (float)($rateData['total'] ?? 0.0),
            final: (float)($rateData['final'] ?? 0.0),
            tax: CalendarTax::fromXml(isset($rateData['tax']) ? $rateData['tax'] : []),
            fee: (float)($rateData['fee'] ?? 0.0),
            prepayment: (float)($rateData['prepayment'] ?? 0.0),
            balanceDue: (float)($rateData['balance_due'] ?? 0.0)
        );
    }
}
