<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class AmountPrice
{
    /**
     * @param array<int, TaxValue> $taxValues
     */
    public function __construct(
        public readonly string $currency,
        public readonly float $netValue,
        public readonly float $grossValue,
        public readonly array $taxValues,
        public readonly ?TaxBreakdown $breakdown,
    ) {}

    public static function map(mixed $data, ?string $defaultCurrency = null): self
    {
        try {
            if (is_numeric($data)) {
                $amount = (float) $data;
                $currency = $defaultCurrency ?? 'EUR';
                return new self($currency, $amount, $amount, [], null);
            }

            if (!is_array($data)) {
                throw new \InvalidArgumentException('AmountPrice data must be array or numeric');
            }

            $currency = $data['Currency'] ?? $defaultCurrency ?? 'EUR';
            $netValue = (float) ($data['NetValue'] ?? $data['GrossValue'] ?? 0.0);
            $grossValue = (float) ($data['GrossValue'] ?? $netValue);

            $taxValues = array_map(
                fn($item) => TaxValue::map($item),
                $data['TaxValues'] ?? []
            );

            $breakdown = isset($data['Breakdown']) && is_array($data['Breakdown'])
                ? TaxBreakdown::map($data['Breakdown'])
                : null;

            return new self($currency, $netValue, $grossValue, $taxValues, $breakdown);
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map AmountPrice: {$e->getMessage()}", 0, $e);
        }
    }
}
