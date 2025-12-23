<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;

class RatesResponse
{
    /**
     * @param Collection<int, Rate> $items
     * @param array<string, mixed> $rateGroups
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly array $rateGroups = [],
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['Rates'] ?? [])
                ->map(fn($item) => Rate::map($item)),
            rateGroups: $data['RateGroups'] ?? [],
            cursor: $data['Cursor'] ?? null
        );
    }
}
