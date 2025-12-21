<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\PricingBlock;

class PricingResponse
{
    public function __construct(
        public readonly string $currency,
        public readonly array $timeUnitStartsUtc,
        public readonly array $baseAmountPrices,
        public readonly array $categoryPrices,
    ) {}

    public static function map(array $data): self
    {
        return new self(
            currency: $data['Currency'] ?? 'EUR',
            timeUnitStartsUtc: $data['TimeUnitStartsUtc'] ?? [],
            baseAmountPrices: $data['BaseAmountPrices'] ?? [],
            categoryPrices: array_map(
                fn($item) => PricingBlock::map($item),
                $data['CategoryPrices'] ?? []
            )
        );
    }
}
