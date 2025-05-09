<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

class PropertyTax
{
    public function __construct(
        public readonly float $vat,
        public readonly float $other,
        public readonly string $otherType
    ) {}

    public static function fromXml(array $data): self
    {
        if (!is_array($data)) {
            $data = [];
        }

        $vat = 0.0;
        $vatType = '';
        if (isset($data['vat'])) {
            if (is_array($data['vat'])) {
                $vat = (float)($data['vat']['#text'] ?? 0.0);
                if (isset($data['vat']['@attributes']) && is_array($data['vat']['@attributes'])) {
                    $vatType = (string)($data['vat']['@attributes']['type'] ?? '');
                }
            } else {
                $vat = (float)$data['vat'];
            }
        }

        $other = 0.0;
        $otherType = '';
        if (isset($data['other'])) {
            $otherData = $data['other'];
            if (is_array($otherData)) {
                $other = (float)($otherData['#text'] ?? 0.0);
                if (isset($otherData['@attributes']) && is_array($otherData['@attributes'])) {
                    $otherType = (string)($otherData['@attributes']['type'] ?? '');
                }
            } elseif (!is_null($otherData) && $otherData !== '') {
                $other = (float)$otherData;
            }
        }

        return new self(
            vat: $vat,
            other: $other,
            otherType: $otherType
        );
    }
}
