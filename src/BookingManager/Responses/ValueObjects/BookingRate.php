<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class BookingRate
{
    public function __construct(
        public readonly float $total,
        public readonly float $final,
        public readonly BookingTax $tax,
        public readonly ?float $prepayment = null,
        public readonly ?float $balance_due = null,
        public readonly ?float $fee = null
    ) {}

    public static function fromXml(array $rateData): self
    {
        try {
            $getFloat = function($key, $default = 0.0) use ($rateData) {
                $value = $rateData[$key] ?? $default;
                return is_numeric($value) ? (float) $value : $default;
            };

            return new self(
                total: $getFloat('total'),
                final: $getFloat('final'),
                tax: BookingTax::fromXml($rateData['tax'] ?? []),
                prepayment: $getFloat('prepayment') ?: null,
                balance_due: $getFloat('balance_due') ?: null,
                fee: $getFloat('fee') ?: null
            );
        } catch (\Exception $e) {
            throw new MappingException('Failed to map BookingRate: ' . $e->getMessage(), 0, $e);
        }
    }
}