<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AvailabilityBlock;

class AvailabilityResponse
{
    /**
     * @param array<int, string> $timeUnitStartsUtc
     * @param Collection<int, AvailabilityBlock> $categoryAvailabilities
     */
    public function __construct(
        public readonly array $timeUnitStartsUtc,
        public readonly Collection $categoryAvailabilities,
    ) {}

    public static function map(array $data): self
    {
        return new self(
            timeUnitStartsUtc: $data['TimeUnitStartsUtc'] ?? [],
            categoryAvailabilities: collect($data['CategoryAvailabilities'] ?? [])
                ->map(fn($item) => AvailabilityBlock::map($item))
        );
    }
}
