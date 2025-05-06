<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class PropertyTax
{
    public function __construct(
        public readonly float $vat,
        public readonly float $other,
        public readonly string $otherType // 'relative' or 'fixed'
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $otherData = $data instanceof Collection ? $data->get('other', []) : ($data['other'] ?? []);
        if ($otherData instanceof Collection) $otherData = $otherData->all(); // Ensure array

        $otherText = is_array($otherData) ? ($otherData['#text'] ?? 0.0) : $otherData;
        $otherAttributesType = is_array($otherData) && isset($otherData['@attributes']['type']) ? $otherData['@attributes']['type'] : '';

        return new self(
            vat: (float) ($data instanceof Collection ? $data->get('vat') : ($data['vat'] ?? 0.0)),
            other: (float) $otherText,
            otherType: (string) $otherAttributesType
        );
    }
}
