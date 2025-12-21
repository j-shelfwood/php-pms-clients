<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AvailabilityBlock;

class AvailabilityResponse
{
    public function __construct(
        public readonly array $timeUnitStartsUtc,
        public readonly array $categoryAvailabilities,
    ) {}

    public static function map(array $data): self
    {
        return new self(
            timeUnitStartsUtc: $data['TimeUnitStartsUtc'] ?? [],
            categoryAvailabilities: array_map(
                fn($item) => AvailabilityBlock::map($item),
                $data['CategoryAvailabilities'] ?? []
            )
        );
    }
}
