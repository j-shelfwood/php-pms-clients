<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

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
        $sourceData = $taxData instanceof Collection ? $taxData : new Collection($taxData);

        $vatInfo = new Collection($sourceData->get('vat', []));
        $otherInfo = new Collection($sourceData->get('other', []));

        $vatAmount = (float) ($vatInfo->get('#text') ?? $vatInfo[0] ?? ($sourceData->get('vat') ?? 0.0));
        $vatValue = (float) ($vatInfo->get('@attributes.value') ?? ($vatInfo->get('@attributes')['value'] ?? 0.0));

        $otherAmount = (float) ($otherInfo->get('#text') ?? $otherInfo[0] ?? ($sourceData->get('other') ?? 0.0));
        $otherType = (string) ($otherInfo->get('@attributes.type') ?? ($otherInfo->get('@attributes')['type'] ?? ''));
        $otherValue = (float) ($otherInfo->get('@attributes.value') ?? ($otherInfo->get('@attributes')['value'] ?? 0.0));

        // Handle cases where vat/other might not be collections but direct values
        if (!($sourceData->get('vat') instanceof Collection || is_array($sourceData->get('vat')))) {
            $vatAmount = (float) ($sourceData->get('vat') ?? 0.0);
            $vatValue = 0.0; // No attributes if it's a direct value
        }
        if (!($sourceData->get('other') instanceof Collection || is_array($sourceData->get('other')))) {
            $otherAmount = (float) ($sourceData->get('other') ?? 0.0);
            $otherType = '';
            $otherValue = 0.0;
        }

        $totalAttribute = $sourceData->get('@attributes');
        $total = (float) ($totalAttribute['total'] ?? $sourceData->get('total') ?? 0.0);

        return new self(
            total: $total,
            other: $otherAmount,
            otherType: $otherType,
            otherValue: $otherValue,
            vat: $vatAmount,
            vatValue: $vatValue,
            final: (float) ($sourceData->get('final') ?? 0.0)
        );
    }
}
