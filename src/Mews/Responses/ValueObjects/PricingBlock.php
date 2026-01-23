<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AmountPrice;

class PricingBlock
{
    /**
     * @param string $resourceCategoryId
     * @param array<int, AmountPrice> $amountPrices
     */
    public function __construct(
        public readonly string $resourceCategoryId,
        public readonly array $amountPrices,
        public readonly ?array $prices,
    ) {}

    public static function map(array $data): self
    {
        try {
            // Mews API returns 'CategoryId' not 'ResourceCategoryId'
            // Try both for backwards compatibility
            $categoryId = $data['CategoryId']
                ?? $data['ResourceCategoryId']
                ?? throw new \InvalidArgumentException('CategoryId or ResourceCategoryId is required');

            return new self(
                resourceCategoryId: $categoryId,
                amountPrices: array_map(
                    fn($item) => AmountPrice::map($item, $data['Currency'] ?? null),
                    $data['AmountPrices'] ?? []
                ),
                prices: $data['Prices'] ?? null,
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map PricingBlock: {$e->getMessage()}", 0, $e);
        }
    }
}
