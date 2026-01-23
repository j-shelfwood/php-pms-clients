<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\PricingBlock;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AmountPrice;

class PricingResponse
{
    /**
     * @param string $currency
     * @param array<int, string> $timeUnitStartsUtc
     * @param array<int, AmountPrice> $baseAmountPrices
     * @param Collection<int, PricingBlock> $categoryPrices
     * @param array<int, string>|null $datesUtc
     * @param array<int, float>|null $basePrices
     * @param array<string, mixed>|null $categoryAdjustments
     * @param array<string, mixed>|null $ageCategoryAdjustments
     * @param float|null $relativeAdjustment
     * @param float|null $absoluteAdjustment
     * @param float|null $emptyUnitAdjustment
     * @param float|null $extraUnitAdjustment
     */
    public function __construct(
        public readonly string $currency,
        public readonly array $timeUnitStartsUtc,
        public readonly array $baseAmountPrices,
        public readonly Collection $categoryPrices,
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
            baseAmountPrices: array_map(
                fn($item) => AmountPrice::map($item, $data['Currency'] ?? 'EUR'),
                $data['BaseAmountPrices'] ?? []
            ),
            categoryPrices: collect($data['CategoryPrices'] ?? [])
                ->map(fn($item) => PricingBlock::map($item)),
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
