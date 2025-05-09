<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



class StayTax
{
    public function __construct(
        public readonly float $total,
        public readonly float $vatAmount,
        public readonly float $vatValue,
        public readonly float $otherAmount,
        public readonly string $otherType,
        public readonly float $otherValue,
        public readonly float $final
    ) {}

    public static function fromXml(array $taxData): self
    {
        $vat = $taxData['vat'] ?? [];
        $vatAmount = is_array($vat) ? (float)($vat['#text'] ?? 0.0) : (float)$vat;
        $vatValue = is_array($vat) ? (float)($vat['@attributes']['value'] ?? 0.0) : 0.0;
        $other = $taxData['other'] ?? [];
        $otherAmount = is_array($other) ? (float)($other['#text'] ?? 0.0) : (float)$other;
        $otherType = is_array($other) ? (string)($other['@attributes']['type'] ?? '') : '';
        $otherValue = is_array($other) ? (float)($other['@attributes']['value'] ?? 0.0) : 0.0;
        return new self(
            total: (float)($taxData['@attributes']['total'] ?? $taxData['total'] ?? 0.0),
            vatAmount: $vatAmount,
            vatValue: $vatValue,
            otherAmount: $otherAmount,
            otherType: $otherType,
            otherValue: $otherValue,
            final: (float)($taxData['final'] ?? 0.0)
        );
    }
}
