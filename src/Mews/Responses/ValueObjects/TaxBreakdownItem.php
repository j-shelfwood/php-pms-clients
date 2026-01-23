<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class TaxBreakdownItem
{
    public function __construct(
        public readonly ?string $taxRateCode,
        public readonly float $netValue,
        public readonly float $taxValue,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                taxRateCode: $data['TaxRateCode'] ?? null,
                netValue: (float) ($data['NetValue'] ?? 0.0),
                taxValue: (float) ($data['TaxValue'] ?? 0.0),
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map TaxBreakdownItem: {$e->getMessage()}", 0, $e);
        }
    }
}
