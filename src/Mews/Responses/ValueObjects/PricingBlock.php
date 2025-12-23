<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;

class PricingBlock
{
    public function __construct(
        public readonly string $resourceCategoryId,
        public readonly array $amountPrices,
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
                amountPrices: $data['AmountPrices'] ?? [],
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map PricingBlock: {$e->getMessage()}", 0, $e);
        }
    }
}
