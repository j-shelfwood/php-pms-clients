<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;



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

    public static function fromXml(array $taxData): self
    {
        $vatInfo = isset($taxData['vat']) ? $taxData['vat'] : [];
        $otherInfo = isset($taxData['other']) ? $taxData['other'] : [];
        $vatAmount = is_array($vatInfo) ? (float)($vatInfo['#text'] ?? 0.0) : (float)$vatInfo;
        $vatValue = is_array($vatInfo) ? (float)($vatInfo['@attributes']['value'] ?? 0.0) : 0.0;
        $otherAmount = is_array($otherInfo) ? (float)($otherInfo['#text'] ?? 0.0) : (float)$otherInfo;
        $otherType = is_array($otherInfo) ? (string)($otherInfo['@attributes']['type'] ?? '') : '';
        $otherValue = is_array($otherInfo) ? (float)($otherInfo['@attributes']['value'] ?? 0.0) : 0.0;
        $total = isset($taxData['@attributes']['total']) ? (float)$taxData['@attributes']['total'] : (float)($taxData['total'] ?? 0.0);
        return new self(
            total: $total,
            other: $otherAmount,
            otherType: $otherType,
            otherValue: $otherValue,
            vat: $vatAmount,
            vatValue: $vatValue,
            final: (float)($taxData['final'] ?? 0.0)
        );
    }
}
