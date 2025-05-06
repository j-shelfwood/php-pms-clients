<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Collection;

class CalendarTax
{
    public function __construct(
        public readonly float $total,
        public readonly float $other,
        public readonly string $otherType,
        public readonly float $otherValue,
        public readonly float $vat,
        public readonly float $vatValue,
        public readonly float $final
    ) {}

    public static function fromXml(Collection|array $taxData): self
    {
        // Handle potential variations where 'vat' or 'other' might be simple floats or arrays with attributes
        $vat = $taxData->get('vat');
        $other = $taxData->get('other');

        $vatAmount = is_array($vat) ? (float) ($vat['#text'] ?? 0.0) : (float) $vat;
        $vatValue = is_array($vat) ? (float) ($vat['@attributes']['value'] ?? 0.0) : 0.0;

        $otherAmount = is_array($other) ? (float) ($other['#text'] ?? 0.0) : (float) $other;
        $otherType = is_array($other) ? (string) ($other['@attributes']['type'] ?? '') : '';
        $otherValue = is_array($other) ? (float) ($other['@attributes']['value'] ?? 0.0) : 0.0;

        return new self(
            total: (float) ($taxData->get('@attributes')['total'] ?? $taxData->get('total') ?? 0.0),
            other: $otherAmount,
            otherType: $otherType,
            otherValue: $otherValue,
            vat: $vatAmount,
            vatValue: $vatValue,
            final: (float) ($taxData->get('final') ?? 0.0)
        );
    }
}
