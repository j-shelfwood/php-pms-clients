<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class BookingTax
{
    public function __construct(
        public readonly float $total,
        public readonly float $vat,
        public readonly float $other,
        public readonly float $final
    ) {}

    public static function fromXml(array $taxData): self
    {
        try {
            $attributes = $taxData['@attributes'] ?? [];

            $getFloat = function($key, $default = 0.0) use ($taxData) {
                $value = $taxData[$key] ?? $default;
                return is_numeric($value) ? (float) $value : $default;
            };

            return new self(
                total: is_numeric($attributes['total'] ?? 0) ? (float) $attributes['total'] : 0.0,
                vat: $getFloat('vat'),
                other: $getFloat('other') ?: $getFloat('tourist'), // Handle deprecated 'tourist' field
                final: $getFloat('final')
            );
        } catch (\Exception $e) {
            throw new MappingException('Failed to map BookingTax: ' . $e->getMessage(), 0, $e);
        }
    }
}