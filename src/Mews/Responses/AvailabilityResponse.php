<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAvailability;

class AvailabilityResponse
{
    /**
     * @param array<int, string> $timeUnitStartsUtc
     * @param Collection<int, ResourceCategoryAvailability> $resourceCategoryAvailabilities
     */
    public function __construct(
        public readonly array $timeUnitStartsUtc,
        public readonly Collection $resourceCategoryAvailabilities,
    ) {}

    public static function map(array $data): self
    {
        return new self(
            timeUnitStartsUtc: $data['TimeUnitStartsUtc'] ?? [],
            resourceCategoryAvailabilities: collect($data['ResourceCategoryAvailabilities'] ?? [])
                ->map(fn($item) => ResourceCategoryAvailability::map($item))
        );
    }
}
