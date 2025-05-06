<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection;

class StayTax
{
    public function __construct(
        public readonly float $total,
        public readonly float $vatAmount,    // Changed from vat
        public readonly float $vatValue,
        public readonly float $otherAmount,  // Changed from other
        public readonly string $otherType,
        public readonly float $otherValue,
        public readonly float $final
    ) {}

    public static function fromXml(Collection|array $taxData): self
    {
        $vat = $taxData->get('vat', []);
        $vatAmount = is_array($vat) ? (float) ($vat['#text'] ?? 0.0) : (float) $vat;
        $vatValue = is_array($vat) ? (float) ($vat['@attributes']['value'] ?? 0.0) : 0.0;

        $other = $taxData->get('other', []);
        $otherAmount = is_array($other) ? (float) ($other['#text'] ?? 0.0) : (float) $other;
        $otherType = is_array($other) ? (string) ($other['@attributes']['type'] ?? '') : '';
        $otherValue = is_array($other) ? (float) ($other['@attributes']['value'] ?? 0.0) : 0.0;

        return new self(
            total: (float) ($taxData->get('@attributes')['total'] ?? $taxData->get('total') ?? 0.0),
            vatAmount: $vatAmount,
            vatValue: $vatValue,
            otherAmount: $otherAmount,
            otherType: $otherType,
            otherValue: $otherValue,
            final: (float) ($taxData->get('final') ?? 0.0)
        );
    }
}
