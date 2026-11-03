<?php

namespace Shelfwood\PhpPms\Mews\Payloads;

/**
 * Price of one time unit (night) on a reservation, sent as an element of
 * Reservation `TimeUnitPrices` on /reservations/add.
 *
 * Mews contract (connector-api operations/reservations.md, _objects.md):
 * Index is the 0-based time unit; Amount carries Currency, exactly one of
 * GrossValue (gross pricing environment) or NetValue (net pricing
 * environment), and TaxCodes, of which only one is allowed with GrossValue.
 */
final class TimeUnitPrice
{
    /**
     * @param string[] $taxCodes
     */
    public function __construct(
        public readonly int $index,
        public readonly string $currency,
        public readonly array $taxCodes,
        public readonly ?float $grossValue = null,
        public readonly ?float $netValue = null,
    ) {
        $this->validate();
    }

    /**
     * @param string[] $taxCodes
     */
    public static function gross(int $index, float $value, string $currency, array $taxCodes): self
    {
        return new self(index: $index, currency: $currency, taxCodes: $taxCodes, grossValue: $value);
    }

    /**
     * @param string[] $taxCodes
     */
    public static function net(int $index, float $value, string $currency, array $taxCodes): self
    {
        return new self(index: $index, currency: $currency, taxCodes: $taxCodes, netValue: $value);
    }

    private function validate(): void
    {
        if ($this->index < 0) {
            throw new \InvalidArgumentException('TimeUnitPrice Index must be >= 0');
        }

        if (trim($this->currency) === '') {
            throw new \InvalidArgumentException('TimeUnitPrice Currency cannot be empty');
        }

        if (($this->grossValue === null) === ($this->netValue === null)) {
            throw new \InvalidArgumentException('TimeUnitPrice requires exactly one of GrossValue or NetValue');
        }

        $value = $this->grossValue ?? $this->netValue;
        if ($value < 0) {
            throw new \InvalidArgumentException('TimeUnitPrice amount must be >= 0');
        }

        if ($this->taxCodes === []) {
            throw new \InvalidArgumentException('TimeUnitPrice TaxCodes cannot be empty');
        }

        foreach ($this->taxCodes as $code) {
            if (! is_string($code) || trim($code) === '') {
                throw new \InvalidArgumentException('TimeUnitPrice TaxCodes must be non-empty strings');
            }
        }

        if ($this->grossValue !== null && count($this->taxCodes) > 1) {
            throw new \InvalidArgumentException('TimeUnitPrice allows only one TaxCode with GrossValue');
        }
    }

    /**
     * @return array{Index: int, Amount: array<string, mixed>}
     */
    public function toArray(): array
    {
        $amount = ['Currency' => $this->currency];

        if ($this->grossValue !== null) {
            $amount['GrossValue'] = $this->grossValue;
        } else {
            $amount['NetValue'] = $this->netValue;
        }

        $amount['TaxCodes'] = array_values($this->taxCodes);

        return [
            'Index' => $this->index,
            'Amount' => $amount,
        ];
    }
}
