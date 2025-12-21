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
            return new self(
                resourceCategoryId: $data['ResourceCategoryId'] ?? throw new \InvalidArgumentException('ResourceCategoryId is required'),
                amountPrices: $data['AmountPrices'] ?? [],
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map PricingBlock: {$e->getMessage()}", 0, $e);
        }
    }
}
