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
        // Additional fields from API
        public readonly ?array $datesUtc,
        public readonly ?array $basePrices,
        public readonly ?array $categoryAdjustments,
        public readonly ?array $ageCategoryAdjustments,
        public readonly ?float $relativeAdjustment,
        public readonly ?float $absoluteAdjustment,
        public readonly ?float $emptyUnitAdjustment,
        public readonly ?float $extraUnitAdjustment,
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
            ),
            // Additional fields from API
            datesUtc: $data['DatesUtc'] ?? null,
            basePrices: $data['BasePrices'] ?? null,
            categoryAdjustments: $data['CategoryAdjustments'] ?? null,
            ageCategoryAdjustments: $data['AgeCategoryAdjustments'] ?? null,
            relativeAdjustment: $data['RelativeAdjustment'] ?? null,
            absoluteAdjustment: $data['AbsoluteAdjustment'] ?? null,
            emptyUnitAdjustment: $data['EmptyUnitAdjustment'] ?? null,
            extraUnitAdjustment: $data['ExtraUnitAdjustment'] ?? null,
        );
    }
}
