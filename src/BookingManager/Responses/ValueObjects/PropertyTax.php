<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Shelfwood\PhpPms\BookingManager\Enums\TaxType;

class PropertyTax
{
    public function __construct(
        public readonly float $vat,
        public readonly float $other,
        public readonly ?TaxType $otherType
    ) {}

    public static function fromXml(array $data): self
    {
        if (!is_array($data)) {
            $data = [];
        }

        $vat = 0.0;
        if (isset($data['vat'])) {
            if (is_array($data['vat'])) {
                $vat = (float)($data['vat']['#text'] ?? 0.0);
            } else {
                $vat = (float)$data['vat'];
            }
        }

        $other = 0.0;
        $otherType = null;
        if (isset($data['other'])) {
            $otherData = $data['other'];
            if (is_array($otherData)) {
                $other = (float)($otherData['#text'] ?? 0.0);
                if (isset($otherData['@attributes']) && is_array($otherData['@attributes']) && isset($otherData['@attributes']['type'])) {
                    $otherType = TaxType::tryFrom((string)$otherData['@attributes']['type']);
                } elseif ($other != 0.0) { // Infer 'relative' if type is missing but value exists
                    $otherType = TaxType::RELATIVE;
                }
            } elseif (!is_null($otherData) && $otherData !== '') {
                $other = (float)$otherData;
                if ($other != 0.0) { // Infer 'relative' if type is missing but value exists
                    $otherType = TaxType::RELATIVE;
                }
            }
        }

        return new self(
            vat: $vat,
            other: $other,
            otherType: $otherType
        );
    }
}
