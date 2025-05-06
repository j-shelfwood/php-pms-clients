<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Collection;

class StayTax
{
    public function __construct(
        public readonly float $total,
        public readonly float $vatAmount,
        public readonly float $vatValue,
        public readonly string $otherType,
        public readonly float $otherAmount,
        public readonly float $otherValue,
        public readonly float $final
    ) {}

    public static function fromXml(Collection|array $taxData): self
    {
        // Note: The XML structure puts the amount as text content and value/type as attributes.
        // Handle potential variations where 'vat' or 'other' might be simple floats or arrays with attributes
        $vat = $taxData->get('vat');
        $other = $taxData->get('other');

        $vatAmount = is_array($vat) ? (float) ($vat['#text'] ?? 0.0) : (float) $vat;
        $vatValue = is_array($vat) ? (float) ($vat['@attributes']['value'] ?? 0.0) : 0.0; // Default if not array

        $otherAmount = is_array($other) ? (float) ($other['#text'] ?? 0.0) : (float) $other;
        $otherType = is_array($other) ? (string) ($other['@attributes']['type'] ?? '') : '';
        $otherValue = is_array($other) ? (float) ($other['@attributes']['value'] ?? 0.0) : 0.0;

        return new self(
            total: (float) ($taxData->get('@attributes')['total'] ?? $taxData->get('total') ?? 0.0), // Handle potential attribute vs element
            vatAmount: $vatAmount,
            vatValue: $vatValue,
            otherType: $otherType,
            otherAmount: $otherAmount,
            otherValue: $otherValue,
            final: (float) ($taxData->get('final') ?? 0.0)
        );
    }
}
