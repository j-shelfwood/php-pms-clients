<?php

namespace Domain\Connections\BookingManager\Responses\ValueObjects;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PropertyTax
{
    public function __construct(
        public readonly float $vat, // Assuming this is percentage or value based on context
        public readonly float $other, // Assuming this is percentage or value
        public readonly string $otherType // 'relative' or 'fixed'
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $otherData = Arr::get($data, 'other', []);

        return new self(
            vat: (float) Arr::get($data, 'vat', 0.0),
            other: (float) (is_array($otherData) ? ($otherData['#text'] ?? 0.0) : $otherData),
            otherType: (string) (is_array($otherData) ? ($otherData['@attributes']['type'] ?? '') : '')
        );
    }
}
