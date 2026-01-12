<?php

namespace Shelfwood\PhpPms\Mews\Responses\ValueObjects;

use Shelfwood\PhpPms\Exceptions\MappingException;
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

class RestrictionConditions
{
    /**
     * @param RestrictionType $type
     * @param string|null $exactRateId
     * @param string|null $baseRateId
     * @param string|null $rateGroupId
     * @param string|null $resourceCategoryId
     * @param string|null $resourceCategoryType
     * @param string|null $startUtc Nullable - indefinite restrictions have no start
     * @param string|null $endUtc Nullable - indefinite restrictions have no end
     * @param array<int, string> $days
     * @param array<string, bool> $hours
     */
    public function __construct(
        public readonly RestrictionType $type,
        public readonly ?string $exactRateId,
        public readonly ?string $baseRateId,
        public readonly ?string $rateGroupId,
        public readonly ?string $resourceCategoryId,
        public readonly ?string $resourceCategoryType,
        public readonly ?string $startUtc,
        public readonly ?string $endUtc,
        public readonly array $days,
        public readonly array $hours,
    ) {}

    public static function map(array $data): self
    {
        try {
            return new self(
                type: isset($data['Type'])
                    ? RestrictionType::from($data['Type'])
                    : throw new \InvalidArgumentException('Type is required'),
                exactRateId: $data['ExactRateId'] ?? null,
                baseRateId: $data['BaseRateId'] ?? null,
                rateGroupId: $data['RateGroupId'] ?? null,
                resourceCategoryId: $data['ResourceCategoryId'] ?? null,
                resourceCategoryType: $data['ResourceCategoryType'] ?? null,
                startUtc: $data['StartUtc'] ?? null,
                endUtc: $data['EndUtc'] ?? null,
                days: $data['Days'] ?? [],
                hours: $data['Hours'] ?? [],
            );
        } catch (\Throwable $e) {
            throw new MappingException("Failed to map RestrictionConditions: {$e->getMessage()}", 0, $e);
        }
    }
}
